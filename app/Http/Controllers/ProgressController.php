<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'logged_date' => ['required', 'date'],
            'weight_kg' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'water_liters' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'steps' => ['nullable', 'integer', 'min:0', 'max:200000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'weight_kg.min' => 'Weight must be at least 20 kg (use your body weight in kilograms).',
            'weight_kg.max' => 'Weight must be 300 kg or less.',
        ]);

        if (
            $validated['weight_kg'] === null
            && $validated['water_liters'] === null
            && $validated['steps'] === null
        ) {
            return redirect()->back()
                ->withErrors(['log' => 'Enter at least weight, water, or steps to save.'])
                ->withInput();
        }

        $request->user()->progressLogs()->updateOrCreate(
            ['logged_date' => $validated['logged_date']],
            [
                'weight_kg' => $validated['weight_kg'] ?? null,
                'water_liters' => $validated['water_liters'] ?? null,
                'steps' => $validated['steps'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        $redirect = $request->headers->get('referer');
        if ($redirect && str_contains($redirect, '/app/progress')) {
            return redirect()->route('fitgo.progress')->with('status', 'progress-saved');
        }

        return redirect()->route('dashboard')->with('status', 'progress-saved');
    }
}
