<?php

namespace App\Services;

use App\Models\MealPlan;
use App\Models\User;

class GroceryListService
{
    /**
     * @return array{items: array<int, string>, source: string}
     */
    public function forUser(User $user): array
    {
        $rows = MealPlan::query()
            ->where('user_id', $user->id)
            ->whereNotNull('ingredients')
            ->get(['ingredients']);

        $ingredients = [];
        foreach ($rows as $row) {
            $list = $row->ingredients;
            if (! is_array($list)) {
                continue;
            }
            foreach ($list as $item) {
                $s = is_string($item) ? trim($item) : trim((string) $item);
                if ($s !== '') {
                    $ingredients[] = $s;
                }
            }
        }

        $ingredients = array_values(array_unique($ingredients));
        sort($ingredients);

        if ($ingredients === []) {
            return [
                'items' => [
                    'Eggs',
                    'Avocado',
                    'Salmon or tuna',
                    'Leafy greens',
                    'Olive oil',
                    'Almonds',
                    'Cheese',
                    'Greek yogurt (if dairy allowed)',
                    'Berries (small portions)',
                ],
                'source' => 'fallback',
            ];
        }

        return [
            'items' => $ingredients,
            'source' => 'meal_plans',
        ];
    }
}
