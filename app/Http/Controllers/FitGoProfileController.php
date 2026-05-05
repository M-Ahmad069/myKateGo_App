<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FitGoProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('fitgo.profile', [
            'user' => $request->user()->load('dietPlan'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->update($validated);

        return redirect()->route('fitgo.profile')->with('status', 'profile-updated');
    }
}
