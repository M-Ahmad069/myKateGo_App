<?php

namespace App\Http\Controllers;

use App\Services\AI\AiCoachChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FitGoCoachController extends Controller
{
    public function show(Request $request): View
    {
        return view('fitgo.coach', [
            'user' => $request->user()->load('dietPlan'),
        ]);
    }

    public function respond(Request $request, AiCoachChatService $chat): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:600'],
        ]);

        return response()->json(
            $chat->send($request->user()->loadMissing('dietPlan'), $validated['message'] ?? null)
        );
    }
}
