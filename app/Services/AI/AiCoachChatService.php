<?php

namespace App\Services\AI;

use App\Models\ChatMessage;
use App\Models\User;
use App\Services\CoachResponderService;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AiCoachChatService
{
    public function __construct(
        protected CoachResponderService $ruleCoach,
    ) {}

    /**
     * @return array{reply: string, chips: array<int, string>}
     */
    public function send(User $user, ?string $message): array
    {
        $text = trim((string) $message);
        if ($text === '') {
            $text = 'Give me one practical tip for today based on my plan.';
        }

        ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $text,
        ]);

        try {
            $reply = $this->callOpenAI($user);
            ChatMessage::create([
                'user_id' => $user->id,
                'role' => 'assistant',
                'content' => $reply,
            ]);

            return [
                'reply' => $reply,
                'chips' => ['Plateau help', 'Keto snacks', 'Hydration'],
            ];
        } catch (\Throwable $e) {
            Log::warning('AiCoachChat OpenAI failed', ['error' => $e->getMessage()]);

            $fallback = $this->ruleCoach->respond($user->loadMissing('dietPlan'), $text);
            ChatMessage::create([
                'user_id' => $user->id,
                'role' => 'assistant',
                'content' => $fallback['reply'],
            ]);

            return $fallback;
        }
    }

    /**
     * @return list<array{role: string, content: string}>
     */
    public function historyPayload(User $user, int $limit = 40): array
    {
        return $user->chatMessages()
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(fn (ChatMessage $m) => [
                'role' => $m->role,
                'content' => $m->content,
            ])
            ->values()
            ->all();
    }

    protected function callOpenAI(User $user): string
    {
        $first = explode(' ', trim($user->name))[0] ?? 'there';
        $plan = $user->dietPlan;
        $macroLine = $plan
            ? sprintf(
                'Daily target ~%d kcal; fat %dg, protein %dg, carbs %dg.',
                $plan->daily_calories,
                $plan->fat_grams,
                $plan->protein_grams,
                $plan->carb_grams
            )
            : 'No saved diet plan row yet—give general keto coaching until plans appear.';

        $tags = implode(', ', $user->coaching_tags ?? []);
        $quiz = $user->quiz_profile ? json_encode($user->quiz_profile, JSON_UNESCAPED_UNICODE) : '';

        $system = <<<SYS
You are FitGo Coach, a supportive keto and fitness guide. Be concise (under 180 words), actionable, and safe—no medical diagnoses.
Address the user as "{$first}". Context: goal {$user->goal->value}, activity {$user->activity_level->value}, workout pref {$user->workout_preference->value}.
Macros: {$macroLine}
Coaching tags: {$tags}
Quiz profile JSON: {$quiz}
SYS;

        $recent = $user->chatMessages()
            ->orderByDesc('id')
            ->limit(24)
            ->get()
            ->reverse();

        $messages = [['role' => 'system', 'content' => $system]];
        foreach ($recent as $m) {
            if ($m->role !== 'user' && $m->role !== 'assistant') {
                continue;
            }
            $messages[] = [
                'role' => $m->role === 'user' ? 'user' : 'assistant',
                'content' => $m->content,
            ];
        }

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'temperature' => 0.55,
            'max_tokens' => 700,
            'messages' => $messages,
        ]);

        $out = trim((string) ($response->choices[0]->message->content ?? ''));
        if ($out === '') {
            throw new \RuntimeException('Empty OpenAI coach reply');
        }

        return $out;
    }
}
