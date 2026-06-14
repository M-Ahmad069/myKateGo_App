<?php

namespace App\Http\Controllers;

use App\Enums\MealType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MealPlansPageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('dietPlan');

        $mealOrder = collect(MealType::cases())->map->value->all();

        $meals = $user->mealPlans()
            ->orderBy('day_of_week')
            ->orderBy('id')
            ->get()
            ->groupBy('day_of_week')
            ->map(function ($dayMeals) use ($mealOrder) {
                return $dayMeals
                    ->sortBy(fn ($m) => array_search($m->meal_type->value, $mealOrder, true))
                    ->values();
            });

        $dayLabels = [];
        for ($dow = 1; $dow <= 7; $dow++) {
            $dayLabels[$dow] = Carbon::now()->startOfWeek()->addDays($dow - 1)->format('l');
        }

        $todayDow = (int) now()->dayOfWeekIso;

        return view('fitgo.meals', [
            'user' => $user,
            'dietPlan' => $user->dietPlan,
            'mealsByDay' => $meals,
            'dayLabels' => $dayLabels,
            'todayDow' => $todayDow,
            'mealCount' => $user->mealPlans()->count(),
        ]);
    }
}
