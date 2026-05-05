<?php

namespace App\Http\Controllers;

use App\Enums\Goal;
use App\Enums\MealType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('dietPlan');

        $todayDow = (int) now()->dayOfWeekIso;

        $mealOrder = collect(MealType::cases())->map->value->all();

        $todayMeals = $user->mealPlans()
            ->where('day_of_week', $todayDow)
            ->get()
            ->sortBy(fn ($m) => array_search($m->meal_type->value, $mealOrder, true))
            ->values();

        $planStart = $user->dietPlan?->created_at ?? now();
        $weekNum = max(1, min(4, ((int) floor(now()->diffInDays($planStart) / 7) % 4) + 1));

        $weekWorkouts = $user->workoutPlans()
            ->where('week_number', $weekNum)
            ->orderBy('day_of_week')
            ->get();

        $latestLog = $user->progressLogs()->orderByDesc('logged_date')->first();

        $startWeight = (float) $user->weight_kg;
        $currentWeight = $latestLog ? (float) $latestLog->weight_kg : $startWeight;
        $lostKg = round($startWeight - $currentWeight, 1);

        $progressChart = $user->progressLogs()
            ->orderBy('logged_date')
            ->get(['logged_date', 'weight_kg']);

        if ($progressChart->isEmpty()) {
            $progressChart = collect([
                (object) ['logged_date' => now()->subDays(6)->toDateString(), 'weight_kg' => $startWeight],
                (object) ['logged_date' => now()->toDateString(), 'weight_kg' => $currentWeight],
            ]);
        }

        $chartLabels = $progressChart->map(fn ($r) => Carbon::parse($r->logged_date)->format('M j'))->values()->all();
        $chartWeights = $progressChart->map(fn ($r) => round((float) $r->weight_kg, 1))->values()->all();

        $goalLabel = match ($user->goal) {
            Goal::LoseWeight => 'Lose Weight',
            Goal::BuildMuscle => 'Build Muscle',
            Goal::GetFit => 'Get Fit',
            Goal::Maintain => 'Maintain & Tone',
        };

        $mealTimeLabel = [
            'breakfast' => '07:30',
            'snack_am' => '10:30',
            'lunch' => '13:00',
            'snack_pm' => '16:00',
            'dinner' => '19:30',
        ];

        $weekStart = now()->startOfWeek();

        return view('dashboard', [
            'user' => $user,
            'todayMeals' => $todayMeals,
            'weekWorkouts' => $weekWorkouts,
            'weekNum' => $weekNum,
            'todayDow' => $todayDow,
            'latestLog' => $latestLog,
            'startWeight' => $startWeight,
            'currentWeight' => $currentWeight,
            'lostKg' => $lostKg,
            'progressChart' => $progressChart,
            'goalLabel' => $goalLabel,
            'mealTimeLabel' => $mealTimeLabel,
            'weekStart' => $weekStart,
            'chartLabels' => $chartLabels,
            'chartWeights' => $chartWeights,
        ]);
    }
}
