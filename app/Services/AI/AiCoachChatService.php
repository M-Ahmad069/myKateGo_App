<?php

namespace App\Services\AI;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AiCoachChatService
{
    public function __construct(
        protected PrebuiltCoachAI $prebuiltCoach,
    ) {}

    /**
     * @return array{reply: string, chips: array<int, string>, engine: string}
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

        $user->loadMissing('dietPlan');

        $apiKey = config('openai.api_key');
        if (filled($apiKey)) {
            try {
                $reply = $this->callOpenAI($user);
                ChatMessage::create([
                    'user_id' => $user->id,
                    'role' => 'assistant',
                    'content' => $reply,
                ]);

                return [
                    'reply' => $reply,
                    'chips' => $this->suggestChips($text),
                    'engine' => 'openai',
                ];
            } catch (\Throwable $e) {
                Log::warning('AiCoachChat OpenAI failed, using pre-built coach', ['error' => $e->getMessage()]);
            }
        }

        $result = $this->prebuiltCoach->reply($user, $text);
        ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => $result['reply'],
        ]);

        return $result;
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

    /**
     * @return array<int, string>
     */
    protected function suggestChips(string $lastMessage): array
    {
        $m = strtolower($lastMessage);
        if (str_contains($m, 'plateau') || str_contains($m, 'stuck')) {
            return ['Check sleep & stress', 'Walking after meals', 'Protein first'];
        }
        if (str_contains($m, 'eat') || str_contains($m, 'meal')) {
            return ['Grocery ideas', 'Snacks on keto', 'Macros explained'];
        }

        return ['What should I eat today?', 'Workout for today', 'Hydration tips'];
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
You are FitGo AI Coach, a friendly expert keto and fitness assistant. Reply naturally like ChatGPT in 2-4 short paragraphs max.
Address the user as "{$first}". Use their real plan data when relevant. No medical diagnoses.
Goal: {$user->goal->value}. Activity: {$user->activity_level->value}. Workout pref: {$user->workout_preference->value}.
Macros: {$macroLine}
Coaching tags: {$tags}
Quiz profile: {$quiz}
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
            'temperature' => 0.65,
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
