<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Support\AiJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class ProgressAnalysisController extends Controller
{
    public function analyse(Request $request): JsonResponse
    {
        $user = $request->user();

        $logs = $user->progressLogs()
            ->orderBy('logged_date')
            ->limit(40)
            ->get()
            ->map(fn ($l) => [
                'date' => $l->logged_date?->toDateString(),
                'weight_kg' => $l->weight_kg,
                'steps' => $l->steps,
                'water_liters' => $l->water_liters,
            ])
            ->values()
            ->all();

        try {
            $goal = $user->goal->value;
            $payload = json_encode($logs, JSON_UNESCAPED_UNICODE);
            $prompt = <<<TXT
Given weight/progress logs for a FitGo user with goal "{$goal}", return ONLY valid JSON with keys:
summary (string, 2 sentences), trend (one of: down, up, flat, unclear), suggestions (array of 3-5 short strings), confidence (one of: low, medium, high).

Logs (oldest to newest): {$payload}
TXT;

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'temperature' => 0.4,
                'max_tokens' => 500,
                'messages' => [
                    ['role' => 'system', 'content' => 'You output only JSON. No markdown fences.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $raw = AiJson::stripFences((string) ($response->choices[0]->message->content ?? ''));
            $data = json_decode($raw, true);
            if (is_array($data) && isset($data['summary'])) {
                return response()->json($data);
            }
        } catch (\Throwable $e) {
            Log::warning('ProgressAnalysis OpenAI failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'summary' => 'Keep logging weight on a steady schedule and stay close to your macro targets.',
            'trend' => 'unclear',
            'suggestions' => [
                'Weigh in at the same time of day',
                'Track steps or walking after meals',
                'Hydrate with electrolytes on training days',
            ],
            'confidence' => 'low',
        ]);
    }
}
