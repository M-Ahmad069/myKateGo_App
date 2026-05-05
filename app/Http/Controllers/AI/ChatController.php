<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\AiCoachChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function send(Request $request, AiCoachChatService $chat): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $result = $chat->send($request->user()->loadMissing('dietPlan'), $validated['message'] ?? null);

        return response()->json($result);
    }

    public function history(Request $request, AiCoachChatService $chat): JsonResponse
    {
        return response()->json([
            'messages' => $chat->historyPayload($request->user()),
        ]);
    }
}
