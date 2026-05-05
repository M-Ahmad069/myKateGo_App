<?php

namespace App\Services\AI;

use App\Enums\Gender;
use App\Enums\Goal;
use App\Models\User;
use App\Support\AiJson;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class DietPlanAI
{
    /**
     * Rich OpenAI JSON with days + summary fields. Throws on total failure (caller falls back to PlanEngine).
     *
     * @return array<string, mixed>
     */
    public function fetchRichPlan(
        User $user,
        int $dailyCalories,
        int $fatGrams,
        int $proteinGrams,
        int $carbGrams,
    ): array {
        $restrictions = implode(', ', $user->diet_restrictions ?? ['none']);
        $genderNote = $user->gender === Gender::Female
            ? 'Female: factor adequate iron, calcium, folate; avoid extreme restriction.'
            : 'Male: support protein and strength retention; adequate zinc and vitamin D context in meal choices.';

        $goalNote = match ($user->goal) {
            Goal::LoseWeight => 'Primary goal is fat loss. Keep net carbs low; maximise satiety.',
            Goal::BuildMuscle => 'Primary goal is muscle gain. Prioritise protein distribution; time carbs sensibly.',
            Goal::GetFit => 'Overall fitness: balanced energy for training days.',
            Goal::Maintain => 'Maintenance / recomposition: stable energy, quality fats.',
        };

        $quizContext = '';
        if ($user->quiz_profile) {
            $quizContext = "\nQuiz profile JSON (use for personalisation): ".json_encode($user->quiz_profile, JSON_UNESCAPED_UNICODE);
        }

        $prompt = <<<PROMPT
You are a certified keto nutritionist. Generate a personalised 7-day keto meal plan.

USER:
- Name: {$user->name}
- Gender: {$user->gender->value}
- Age: {$user->age}, Height: {$user->height_cm} cm
- Weight: {$user->weight_kg} kg → Target: {$user->target_weight_kg} kg
- Activity: {$user->activity_level->value}
- Workout preference: {$user->workout_preference->value}
- Diet restrictions: {$restrictions}
- Sex-specific signal: {$user->gender_specific_data}

{$quizContext}

CALCULATED TARGETS (stay within ~50 kcal/day of daily calories; macros close):
- Daily calories: {$dailyCalories} kcal
- Fat ~{$fatGrams} g, Protein ~{$proteinGrams} g, Carbs ~{$carbGrams} g net-style allocation per day

GOAL: {$goalNote}
GENDER NOTE: {$genderNote}

RULES:
1. Exactly 5 meals per day: breakfast, snack_am, lunch, snack_pm, dinner
2. Vary meals across days — avoid repeating the same main protein within 3 days
3. Respect dietary restrictions strictly
4. Practical meals under ~30 min prep
5. Include portion sizes in descriptions
6. Each meal: prep_time_min, optional ingredients array

Return ONLY valid JSON (no markdown):
{
  "plan_summary": "2-3 sentences why this plan suits this user",
  "key_foods": ["..."],
  "foods_to_avoid": ["..."],
  "daily_tip": "one tip",
  "days": [
    {
      "day": 1,
      "day_name": "Monday",
      "meals": [
        {
          "type": "breakfast",
          "name": "",
          "description": "",
          "prep_time_min": 10,
          "calories": 0,
          "fat_g": 0,
          "protein_g": 0,
          "carb_g": 0,
          "ingredients": ["..."]
        }
      ]
    }
  ]
}
PROMPT;

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'max_tokens' => 4000,
            'temperature' => 0.65,
            'messages' => [
                ['role' => 'system', 'content' => 'You return only valid JSON for meal plans. No markdown fences.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $raw = $response->choices[0]->message->content ?? '';
        $raw = AiJson::stripFences($raw);
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
            Log::warning('DietPlanAI invalid JSON', ['error' => json_last_error_msg()]);

            throw new \RuntimeException('Invalid JSON from OpenAI diet plan');
        }

        if (empty($data['days']) || ! is_array($data['days'])) {
            throw new \RuntimeException('OpenAI diet plan missing days');
        }

        $data['source'] = 'openai_rich';
        $data['model'] = 'gpt-4o-mini';

        return $data;
    }
}
