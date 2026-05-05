<?php

namespace App\Services;

use App\Enums\Gender;
use App\Enums\Goal;
use App\Enums\MealType;
use App\Models\DietPlan;
use App\Models\MealPlan;
use App\Models\User;
use App\Services\AI\DietPlanAI;
use Illuminate\Support\Facades\Log;

class DietPlanService
{
    public function __construct(
        protected PlanEngineService $planEngine,
        protected DietPlanAI $dietPlanAI,
    ) {}

    public const FAT_PCT = 72;

    public const PROTEIN_PCT = 23;

    public const CARB_PCT = 5;

    public function generate(User $user): array
    {
        $user->dietPlan?->mealPlans()->delete();
        $user->dietPlan?->delete();

        $bmr = $this->bmr($user);
        $tdee = $bmr * $user->activity_level->tdeeMultiplier();
        $dailyCalories = $this->applyGoalCalories($tdee, $user->goal);
        $fatG = (int) round($dailyCalories * self::FAT_PCT / 100 / 9);
        $proteinG = (int) round($dailyCalories * self::PROTEIN_PCT / 100 / 4);
        $carbG = (int) round($dailyCalories * self::CARB_PCT / 100 / 4);

        $diffKg = abs((float) $user->weight_kg - (float) $user->target_weight_kg);
        $weeks = (int) max(4, min(52, ceil($diffKg / 0.5)));

        $planJson = $this->resolveMealPlanDocument($user, $dailyCalories, $fatG, $proteinG, $carbG);
        $planJson['days'] ??= [];

        $dietPlan = DietPlan::create([
            'user_id' => $user->id,
            'daily_calories' => $dailyCalories,
            'fat_pct' => self::FAT_PCT,
            'protein_pct' => self::PROTEIN_PCT,
            'carb_pct' => self::CARB_PCT,
            'fat_grams' => $fatG,
            'protein_grams' => $proteinG,
            'carb_grams' => $carbG,
            'plan_type' => 'Standard Keto',
            'estimated_weeks_to_goal' => $weeks,
            'raw_plan' => $planJson,
            'plan_summary' => isset($planJson['plan_summary']) ? (string) $planJson['plan_summary'] : null,
            'key_foods' => $this->normalizeStringList($planJson['key_foods'] ?? null),
            'foods_to_avoid' => $this->normalizeStringList($planJson['foods_to_avoid'] ?? null),
            'daily_tip' => isset($planJson['daily_tip']) ? (string) $planJson['daily_tip'] : null,
        ]);

        foreach ($planJson['days'] as $dayBlock) {
            $dayNum = (int) ($dayBlock['day'] ?? 1);
            $dayOfWeek = max(1, min(7, $dayNum));

            foreach ($dayBlock['meals'] ?? [] as $meal) {
                $typeRaw = (string) ($meal['type'] ?? 'lunch');
                $mealType = MealType::fromAiType($typeRaw);

                MealPlan::create([
                    'diet_plan_id' => $dietPlan->id,
                    'user_id' => $user->id,
                    'day_of_week' => $dayOfWeek,
                    'meal_type' => $mealType,
                    'meal_name' => (string) ($meal['name'] ?? 'Keto meal'),
                    'description' => isset($meal['description']) ? (string) $meal['description'] : null,
                    'calories' => (int) ($meal['calories'] ?? 0),
                    'fat_g' => (int) ($meal['fat_g'] ?? 0),
                    'protein_g' => (int) ($meal['protein_g'] ?? 0),
                    'carb_g' => (int) ($meal['carb_g'] ?? 0),
                    'day_name' => isset($dayBlock['day_name']) ? (string) $dayBlock['day_name'] : null,
                    'prep_time_min' => isset($meal['prep_time_min']) ? (int) $meal['prep_time_min'] : null,
                    'ingredients' => isset($meal['ingredients']) && is_array($meal['ingredients'])
                        ? array_values(array_map('strval', $meal['ingredients']))
                        : null,
                ]);
            }
        }

        return [
            'daily_calories' => $dailyCalories,
            'fat_g' => $fatG,
            'protein_g' => $proteinG,
            'carb_g' => $carbG,
            'plan_type' => $dietPlan->plan_type,
            'estimated_weeks_to_goal' => $weeks,
        ];
    }

    protected function bmr(User $user): float
    {
        $w = (float) $user->weight_kg;
        $h = (float) $user->height_cm;
        $a = (int) $user->age;

        if ($user->gender === Gender::Male) {
            return 10 * $w + 6.25 * $h - 5 * $a + 5;
        }

        return 10 * $w + 6.25 * $h - 5 * $a - 161;
    }

    protected function applyGoalCalories(float $tdee, Goal $goal): int
    {
        $factor = match ($goal) {
            Goal::LoseWeight => 0.8,
            Goal::BuildMuscle => 1.1,
            Goal::GetFit => 0.95,
            Goal::Maintain => 1.0,
        };

        return max(1200, (int) round($tdee * $factor));
    }

    /**
     * Full document stored as diet_plans.raw_plan (engine metadata + days, or OpenAI + days).
     *
     * @return array<string, mixed>
     */
    protected function resolveMealPlanDocument(User $user, int $dailyCalories, int $fatG, int $proteinG, int $carbG): array
    {
        $driver = (string) config('fitgo.meal_plan_driver', 'openai');

        if ($driver === 'openai') {
            try {
                $rich = $this->dietPlanAI->fetchRichPlan($user, $dailyCalories, $fatG, $proteinG, $carbG);
                if (! empty($rich['days']) && is_array($rich['days'])) {
                    return $rich;
                }
            } catch (\Throwable $e) {
                Log::warning('DietPlanAI failed; falling back to PlanEngine', ['error' => $e->getMessage()]);
            }
        }

        return $this->planEngine->buildSevenDay($user, $dailyCalories, $fatG, $proteinG, $carbG);
    }

    /**
     * @return array<int, string>|null
     */
    protected function normalizeStringList(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }
        if (is_array($value)) {
            $out = [];
            foreach ($value as $item) {
                if (is_string($item) && $item !== '') {
                    $out[] = $item;
                } elseif (is_scalar($item)) {
                    $out[] = (string) $item;
                }
            }

            return $out === [] ? null : array_values($out);
        }
        if (is_string($value)) {
            $trim = trim($value);

            return $trim === '' ? null : [$trim];
        }

        return null;
    }
}
