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

        $todayLog = $user->progressLogs()
            ->whereDate('logged_date', now()->toDateString())
            ->first();

        $latestWeightLog = $user->progressLogs()
            ->whereNotNull('weight_kg')
            ->orderByDesc('logged_date')
            ->first();

        $startWeight = (float) $user->weight_kg;
        $currentWeight = $latestWeightLog
            ? (float) $latestWeightLog->weight_kg
            : $startWeight;
        $lostKg = round($startWeight - $currentWeight, 1);

        $logs = $user->progressLogs()
            ->orderByDesc('logged_date')
            ->limit(60)
            ->get();

        $chartLogs = $user->progressLogs()
            ->whereNotNull('weight_kg')
            ->orderBy('logged_date')
            ->get(['logged_date', 'weight_kg']);

        if ($chartLogs->isEmpty()) {
            $chartLabels = [now()->format('M j')];
            $chartWeights = [round($startWeight, 1)];
        } else {
            $chartLabels = $chartLogs->map(fn ($r) => Carbon::parse($r->logged_date)->format('M j'))->values()->all();
            $chartWeights = $chartLogs->map(fn ($r) => round((float) $r->weight_kg, 1))->values()->all();
        }

        return view('fitgo.progress', [
            'user' => $user,
            'logs' => $logs,
            'todayLog' => $todayLog,
            'latestLog' => $latestWeightLog,
            'logCount' => $user->progressLogs()->count(),
            'startWeight' => $startWeight,
            'currentWeight' => $currentWeight,
            'lostKg' => $lostKg,
            'chartLabels' => $chartLabels,
            'chartWeights' => $chartWeights,
        ]);
    }
}
