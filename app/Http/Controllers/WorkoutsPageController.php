<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkoutsPageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('dietPlan');

        $planStart = $user->dietPlan?->created_at ?? now();
        $currentWeek = max(1, min(4, ((int) floor(now()->diffInDays($planStart) / 7) % 4) + 1));
        $todayDow = (int) now()->dayOfWeekIso;

        $workoutsByWeek = $user->workoutPlans()
            ->orderBy('week_number')
            ->orderBy('day_of_week')
            ->get()
            ->groupBy('week_number');

        $dayLabels = [];
        for ($dow = 1; $dow <= 7; $dow++) {
            $dayLabels[$dow] = Carbon::now()->startOfWeek()->addDays($dow - 1)->format('l');
        }

        $typeIcons = [
            'strength' => ['🏋️', 'Strength'],
            'cardio' => ['🏃', 'Cardio'],
            'hiit' => ['⚡', 'HIIT'],
            'rest' => ['😴', 'Rest'],
            'flexibility' => ['🧘', 'Flexibility'],
        ];

        return view('fitgo.workouts', [
            'user' => $user,
            'workoutsByWeek' => $workoutsByWeek,
            'workoutCount' => $user->workoutPlans()->count(),
            'currentWeek' => $currentWeek,
            'todayDow' => $todayDow,
            'dayLabels' => $dayLabels,
            'typeIcons' => $typeIcons,
        ]);
    }
}
