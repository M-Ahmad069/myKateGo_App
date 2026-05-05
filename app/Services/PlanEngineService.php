<?php

namespace App\Services;

use App\Enums\Goal;
use App\Models\User;

class PlanEngineService
{
    public const ENGINE_VERSION = '1';

    /**
     * Build a 7-day keto-shaped meal plan JSON (same outer shape as legacy AI output).
     *
     * @return array<string, mixed>
     */
    public function buildSevenDay(User $user, int $dailyCalories, int $fatG, int $proteinG, int $carbG): array
    {
        $restrictions = $user->diet_restrictions ?? [];
        $vegetarian = in_array('vegetarian', $restrictions, true);
        $segment = (string) ($user->plan_segment ?? 'default');
        $templateId = $this->resolveTemplateId($user->goal ?? Goal::GetFit, $vegetarian, $segment);

        $pools = $vegetarian ? $this->vegetarianMealPools() : $this->omnivoreMealPools();
        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        $days = [];
        for ($d = 1; $d <= 7; $d++) {
            $rot = ($d - 1) % 7;
            $meals = $this->assembleDayMeals($dailyCalories, $fatG, $proteinG, $carbG, $pools, $rot);
            $days[] = [
                'day' => $d,
                'label' => $dayNames[$d - 1],
                'meals' => $meals,
            ];
        }

        return [
            'source' => 'fitgo_engine',
            'engine_version' => self::ENGINE_VERSION,
            'template_id' => $templateId,
            'inputs' => [
                'plan_segment' => $segment,
                'goal' => $user->goal?->value,
                'vegetarian' => $vegetarian,
                'daily_calories' => $dailyCalories,
            ],
            'days' => $days,
        ];
    }

    protected function resolveTemplateId(Goal $goal, bool $vegetarian, string $segment): string
    {
        $base = $vegetarian ? 'keto_veg' : 'keto_std';
        $g = match ($goal) {
            Goal::LoseWeight => 'cut',
            Goal::BuildMuscle => 'build',
            Goal::Maintain => 'maint',
            default => 'balance',
        };

        return "{$base}_{$g}_".substr(preg_replace('/[^a-z0-9_]+/i', '_', $segment), 0, 48);
    }

    /**
     * @param  array<string, array<int, array{name: string, description: string}>>  $pools
     * @return array<int, array<string, mixed>>
     */
    protected function assembleDayMeals(int $dailyCalories, int $fatG, int $proteinG, int $carbG, array $pools, int $rotation): array
    {
        $slots = [
            'breakfast' => 0.24,
            'snack_am' => 0.10,
            'lunch' => 0.34,
            'snack_pm' => 0.08,
            'dinner' => 0.34,
        ];

        $mealTypes = array_keys($slots);
        $out = [];
        $idx = 0;

        foreach ($slots as $type => $frac) {
            $cals = max(80, (int) round($dailyCalories * $frac));
            $f = max(3, (int) round($fatG * $frac));
            $p = max(6, (int) round($proteinG * $frac));
            $c = max(0, (int) round($carbG * $frac));

            $poolKey = match ($type) {
                'breakfast' => 'breakfast',
                'snack_am', 'snack_pm' => 'snack',
                default => 'main',
            };

            $list = $pools[$poolKey] ?? [['name' => 'Keto-friendly meal', 'description' => 'High fat, moderate protein, low carb']];
            $pick = $list[($rotation + $idx) % count($list)];
            $idx++;

            $out[] = [
                'type' => $type,
                'name' => $pick['name'],
                'description' => $pick['description'],
                'calories' => $cals,
                'fat_g' => $f,
                'protein_g' => $p,
                'carb_g' => $c,
            ];
        }

        return $out;
    }

    /**
     * @return array<string, array<int, array{name: string, description: string}>>
     */
    protected function omnivoreMealPools(): array
    {
        return [
            'breakfast' => [
                ['name' => 'Bacon & egg skillet', 'description' => 'Eggs cooked in butter with bacon and avocado'],
                ['name' => 'Salmon scramble', 'description' => 'Scrambled eggs with smoked salmon and cream cheese'],
                ['name' => 'Greek yogurt bowl', 'description' => 'Full-fat Greek yogurt with crushed walnuts and chia'],
                ['name' => 'Steak & eggs', 'description' => 'Thin sirloin with two eggs and sautéed spinach'],
                ['name' => 'Cottage egg muffins', 'description' => 'Egg muffins with cheddar and ham'],
                ['name' => 'Avocado feta omelette', 'description' => 'Three-egg omelette with feta and tomato'],
                ['name' => 'Chia coco pudding', 'description' => 'Chia soaked in coconut cream with vanilla'],
            ],
            'main' => [
                ['name' => 'Grilled chicken Caesar (no croutons)', 'description' => 'Romaine, parmesan, anchovy-garlic dressing'],
                ['name' => 'Beef stir-fry bowl', 'description' => 'Ground beef with broccoli, sesame oil'],
                ['name' => 'Pork chops & green beans', 'description' => 'Pan-seared chops with buttered beans'],
                ['name' => 'Tuna salad lettuce wraps', 'description' => 'Tuna, mayo, celery in butter lettuce'],
                ['name' => 'Baked salmon tray', 'description' => 'Salmon, asparagus, lemon butter'],
                ['name' => 'Chicken thigh sheet pan', 'description' => 'Skin-on thighs with zucchini and herbs'],
                ['name' => 'Shrimp garlic butter', 'description' => 'Shrimp with garlic butter over cauliflower rice'],
                ['name' => 'Lamb chops & salad', 'description' => 'Herb lamb with mixed greens'],
                ['name' => 'Turkey meatball marinara', 'description' => 'Baked meatballs in low-sugar sauce'],
                ['name' => 'Cod with caper butter', 'description' => 'White fish with capers and olive oil'],
                ['name' => 'BBQ pulled pork bowl', 'description' => 'Slow-cooked pork on coleslaw'],
                ['name' => 'Chicken satay skewers', 'description' => 'Thigh pieces with peanut dipping sauce (keto)'],
                ['name' => 'Ribeye & mushrooms', 'description' => 'Seared steak with creamed mushrooms'],
                ['name' => 'Sausage & peppers', 'description' => 'Italian sausage with peppers and onion'],
            ],
            'snack' => [
                ['name' => 'Cheese cubes & olives', 'description' => 'Aged cheddar with mixed olives'],
                ['name' => 'Macadamia handful', 'description' => 'Raw macadamias with sea salt'],
                ['name' => 'Celery with almond butter', 'description' => 'Celery sticks, no-sugar almond butter'],
                ['name' => 'Prosciutto roll-ups', 'description' => 'Prosciutto wrapped around cream cheese'],
                ['name' => 'Half avocado + salt', 'description' => 'Simple high-fat snack'],
                ['name' => 'Pork rinds & guac', 'description' => 'Crispy rinds with smashed avocado'],
                ['name' => 'Boiled eggs (2)', 'description' => 'Hard-boiled eggs with pink salt'],
            ],
        ];
    }

    /**
     * @return array<string, array<int, array{name: string, description: string}>>
     */
    protected function vegetarianMealPools(): array
    {
        return [
            'breakfast' => [
                ['name' => 'Tofu scramble', 'description' => 'Firm tofu with nutritional yeast and spinach'],
                ['name' => 'Coconut chia parfait', 'description' => 'Chia with coconut cream and berries (small portion)'],
                ['name' => 'Avocado tofu toast (keto bread)', 'description' => 'Low-carb bread with tofu spread'],
                ['name' => 'Mushroom & cheese frittata', 'description' => 'Eggs baked with mushrooms and gruyère'],
            ],
            'main' => [
                ['name' => 'Paneer tikka bowl', 'description' => 'Grilled paneer with cauliflower rice'],
                ['name' => 'Eggplant parmesan (keto)', 'description' => 'Baked eggplant with mozzarella and marinara'],
                ['name' => 'Zucchini noodle alfredo', 'description' => 'Zoodles with parmesan cream sauce'],
                ['name' => 'Tempeh taco bowl', 'description' => 'Crumbled tempeh with avocado and slaw'],
                ['name' => 'Stuffed bell peppers', 'description' => 'Peppers filled with cheese, eggs, and herbs'],
                ['name' => 'Coconut curry tofu', 'description' => 'Tofu and green beans in coconut curry'],
                ['name' => 'Greek salad + halloumi', 'description' => 'Large salad with grilled halloumi'],
                ['name' => 'Cauliflower risotto', 'description' => 'Cauliflower with parmesan and mushrooms'],
            ],
            'snack' => [
                ['name' => 'Almonds & dark chocolate square', 'description' => 'High-cacao chocolate, small portion'],
                ['name' => 'Cucumber tzatziki', 'description' => 'Cucumber sticks with full-fat yogurt dip'],
                ['name' => 'Seaweed snacks + macadamia', 'description' => 'Crispy nori with nuts'],
            ],
        ];
    }
}
