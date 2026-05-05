<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgressPageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('dietPlan');

        $latestLog = $user->progressLogs()->orderByDesc('logged_date')->first();

        $startWeight = (float) $user->weight_kg;
        $currentWeight = $latestLog ? (float) $latestLog->weight_kg : $startWeight;
        $lostKg = round($startWeight - $currentWeight, 1);

        $progressChart = $user->progressLogs()
            ->orderBy('logged_date')
            ->get(['logged_date', 'weight_kg', 'water_liters', 'steps', 'notes']);

        if ($progressChart->isEmpty()) {
            $progressChart = collect([
                (object) ['logged_date' => now()->subDays(6)->toDateString(), 'weight_kg' => $startWeight, 'water_liters' => null, 'steps' => null, 'notes' => null],
                (object) ['logged_date' => now()->toDateString(), 'weight_kg' => $currentWeight, 'water_liters' => null, 'steps' => null, 'notes' => null],
            ]);
        }

        $chartLabels = $progressChart->map(fn ($r) => Carbon::parse($r->logged_date)->format('M j'))->values()->all();
        $chartWeights = $progressChart->map(fn ($r) => round((float) $r->weight_kg, 1))->values()->all();

        return view('fitgo.progress', [
            'user' => $user,
            'logs' => $user->progressLogs()->orderByDesc('logged_date')->limit(60)->get(),
            'latestLog' => $latestLog,
            'startWeight' => $startWeight,
            'currentWeight' => $currentWeight,
            'lostKg' => $lostKg,
            'chartLabels' => $chartLabels,
            'chartWeights' => $chartWeights,
        ]);
    }
}
