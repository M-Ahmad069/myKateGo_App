<?php

namespace App\Http\Controllers;

use App\Models\GroceryCheck;
use App\Services\GroceryListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroceryPageController extends Controller
{
    public function index(Request $request, GroceryListService $grocery): View
    {
        $user = $request->user();
        $list = $grocery->forUser($user);

        $checkedMap = $user->groceryChecks()
            ->whereIn('item', $list['items'])
            ->pluck('checked', 'item');

        $itemRows = [];
        foreach ($list['items'] as $name) {
            $itemRows[] = [
                'name' => $name,
                'checked' => (bool) ($checkedMap[$name] ?? false),
            ];
        }

        return view('fitgo.grocery', [
            'user' => $user,
            'itemRows' => $itemRows,
            'source' => $list['source'],
            'mealCount' => $user->mealPlans()->count(),
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item' => ['required', 'string', 'max:255'],
            'checked' => ['required', 'boolean'],
        ]);

        $user = $request->user();

        GroceryCheck::updateOrCreate(
            [
                'user_id' => $user->id,
                'item' => $validated['item'],
            ],
            [
                'checked' => $validated['checked'],
            ]
        );

        return response()->json([
            'ok' => true,
            'item' => $validated['item'],
            'checked' => $validated['checked'],
        ]);
    }
}
