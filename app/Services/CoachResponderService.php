<?php

namespace App\Services;

use App\Models\User;

class CoachResponderService
{
    /**
     * @return array{reply: string, chips: array<int, string>}
     */
    public function respond(User $user, ?string $message): array
    {
        $msg = strtolower(trim((string) $message));
        $tags = array_values($user->coaching_tags ?? []);
        $cal = $user->dietPlan?->daily_calories;

        foreach ($this->priorityRules($user, $tags, $cal) as [$needles, $text, $chips]) {
            foreach ($needles as $n) {
                if ($n !== '' && str_contains($msg, $n)) {
                    return ['reply' => $text, 'chips' => $chips];
                }
            }
        }

        if ($msg !== '' && preg_match('/\b(hungry|craving|sweet|snack)\b/', $msg)) {
            return [
                'reply' => $cal
                    ? "When cravings hit on keto, prioritize protein plus a small high-fat snack (nuts, cheese) and water with electrolytes. Your target is roughly {$cal} kcal/day—small adjustments beat all-or-nothing swings."
                    : 'When cravings hit, aim for protein first, then a small high-fat snack, hydration, and electrolytes. Consistency beats perfection.',
                'chips' => ['What counts as keto snacks?', 'I missed a meal — what now?'],
            ];
        }

        if ($tags && str_contains(implode(',', $tags), 'female_horm_pcos')) {
            return [
                'reply' => 'PCOS-friendly pacing: favor protein at each meal, steady walking after meals when you can, and strength training as your primary cardio anchor. Hydration plus sleep will move the needle as much as the gym.',
                'chips' => ['Walking after meals?', 'Strength vs cardio'],
            ];
        }

        if (in_array('recovery_high', $tags, true)) {
            return [
                'reply' => 'Recovery mode: three hard sessions a week beats six mediocre ones when sleep or stress are high. Keep protein on target and treat walking as legitimate training—you still progress.',
                'chips' => ['How low can volume go?', 'Protein targets'],
            ];
        }

        $goalLine = match ($user->goal?->value ?? '') {
            'lose_weight' => 'With a calorie deficit plus your keto macros, predictable meals and walking stack up fastest.',
            'build_muscle' => 'Building on keto means nailing protein and keeping strength work gradual—small weekly progressions compound.',
            'maintain' => 'Maintenance thrives on repeatable meals, consistent training rhythm, and honest logging.',
            default => 'Momentum comes from repeatable meals and showing up—even short sessions count.',
        };

        return [
            'reply' => "Here's your cue: {$goalLine}".($cal ? " Your calorie anchor is around {$cal} kcal/day—adjust only when weight trend is clear across 10–14 days." : ''),
            'chips' => ['Plateau for 2 weeks', 'Hydration on keto'],
        ];
    }

    /**
     * @param  array<int, string>  $tags
     * @return list<array{0:list<string>,1:string,2:array<int, string>}>
     */
    protected function priorityRules(User $user, array $tags, ?int $cal): array
    {
        $tagStr = ','.implode(',', $tags).',';

        return [
            [['plateau', 'stuck'], 'Plateaus usually mean adherence drift or needing a deload—not always fewer calories. Log weight as a weekly average and keep protein tight; if lifts feel beat up, swap one HIIT day for brisk walking.'.($cal ? " Hold near {$cal} kcal/day for two weeks unless trend clearly reverses." : ''), ['Check-in: sleep & salt', 'Adjust calories how?']],
            [['headache', 'keto flu'], 'Likely electrolytes plus hydration. Aim for sodium on food, potassium from whole foods where possible, magnesium at night—especially in the first two keto weeks.', ['Electrolyte cheat sheet', 'When headaches persist']],
            [['water', 'hydrat'], 'Shoot for clarity over perfection: sip steadily, add electrolytes on training days, and match extra carbs (if any) with more fluid.', ['Signs you need electrolytes']],
            [['fast', 'intermitt'], 'Intermittent fasting can pair with keto, but protein still matters inside your eating window. If energy crashes, shorten the fasting window—not your protein.', ['Protein timing', 'Training fasted']],
            [['walk', 'steps'], $tagStr !== '' && str_contains($tagStr, 'pcos')
                ? 'Walking is medicine for insulin sensitivity—10–15 minutes after meals compounds nicely with your profile.'
                : 'Daily steps are free progress insurance: post-meal walks help appetite and recovery without eating into muscle.',
                ['How many steps to start?']],
        ];
    }
}
