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
            'weight_kg' => ['nullable', 'numeric', 'min:30', 'max:300'],
            'water_liters' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'steps' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $request->user()->progressLogs()->updateOrCreate(
            ['logged_date' => $validated['logged_date']],
            [
                'weight_kg' => $validated['weight_kg'] ?? null,
                'water_liters' => $validated['water_liters'] ?? null,
                'steps' => $validated['steps'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()->back()->with('status', 'progress-saved');
    }
}
