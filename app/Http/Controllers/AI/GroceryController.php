<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\MealPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroceryController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $rows = MealPlan::query()
            ->where('user_id', $userId)
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
            return response()->json([
                'items' => [
                    'Eggs', 'Avocado', 'Salmon or tuna', 'Leafy greens', 'Olive oil',
                    'Almonds', 'Cheese', 'Greek yogurt (if dairy allowed)', 'Berries (small portions)',
                ],
                'source' => 'fallback',
            ]);
        }

        return response()->json([
            'items' => $ingredients,
            'source' => 'meal_plans',
        ]);
    }
}
