<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\CoachResponderService;
use Carbon\Carbon;

/**
 * Conversational fitness coach (no external API). Used when OpenAI is unavailable
 * or as the primary "pre-built AI" for demos and coursework.
 */
class PrebuiltCoachAI
{
    public function __construct(
        protected CoachResponderService $keywordCoach,
    ) {}

    /**
     * @return array{reply: string, chips: array<int, string>, engine: string}
     */
    public function reply(User $user, string $message): array
    {
        $user->loadMissing('dietPlan');
        $msg = strtolower(trim($message));
        $first = explode(' ', trim($user->name))[0] ?? 'there';
        $cal = $user->dietPlan?->daily_calories;
        $protein = $user->dietPlan?->protein_grams;

        if ($msg === '' || preg_match('/^(hi|hello|hey|yo|hiya|good morning|good evening|good afternoon)\b/', $msg)) {
            return $this->wrap(
                "Hi {$first}! I'm FitGo AI Coach — your personalised keto and training assistant. "
                .'Ask me about meals, workouts, plateaus, cravings, hydration, or sleep. '
                .($cal ? "I see your plan targets about {$cal} kcal/day with {$protein}g protein." : 'Complete the quiz first so I can tailor advice to your plan.'),
                ['What should I eat today?', 'I hit a plateau', 'Best keto snacks'],
            );
        }

        if (preg_match('/\b(who are you|what are you|your name|are you ai|are you real|bot)\b/', $msg)) {
            return $this->wrap(
                "I'm FitGo's built-in AI coach — a smart assistant trained on keto nutrition and training logic. "
                ."I use your quiz answers, macros, and goals to give practical guidance (not medical diagnosis). "
                .'How can I help you right now?',
                ['Explain my macros', 'Workout for today', 'Hydration tips'],
            );
        }

        if (preg_match('/\b(thank|thanks|thx|appreciate)\b/', $msg)) {
            return $this->wrap(
                "You're welcome, {$first}! Small consistent steps beat perfect weeks. Check your dashboard after you log weight or water — it keeps the plan honest.",
                ['Tip for tomorrow', 'Meal ideas', 'Sleep and recovery'],
            );
        }

        if (preg_match('/\b(what should i eat|eat today|meal today|breakfast|lunch|dinner|food today)\b/', $msg)) {
            $meals = $user->mealPlans()
                ->where('day_of_week', (int) now()->dayOfWeekIso)
                ->orderBy('id')
                ->get();

            if ($meals->isNotEmpty()) {
                $lines = $meals->map(fn ($m) => '• '.$m->meal_name.' ('.$m->meal_type->value.', '.$m->calories.' kcal)')->implode("\n");

                return $this->wrap(
                    "Here's what's on your plan for ".Carbon::now()->format('l').":\n\n{$lines}\n\n"
                    .'Stick to these portions first — consistency matters more than adding extra snacks.',
                    ['Grocery list ideas', 'I have cravings', 'Adjust calories?'],
                );
            }

            return $this->wrap(
                'I do not see meals saved for your account yet. Finish plan generation from the quiz, then ask again — I will read your actual meal rows from the database.',
                ['Retake quiz', 'How many calories?', 'Keto basics'],
            );
        }

        if (preg_match('/\b(workout|exercise|training|gym|hiit|cardio|lift)\b/', $msg)) {
            $planStart = $user->dietPlan?->created_at ?? now();
            $weekNum = max(1, min(4, ((int) floor(now()->diffInDays($planStart) / 7) % 4) + 1));
            $today = $user->workoutPlans()
                ->where('week_number', $weekNum)
                ->where('day_of_week', (int) now()->dayOfWeekIso)
                ->first();

            if ($today) {
                $type = $today->workout_type->value;
                $name = $today->workout_name;
                $dur = (int) $today->duration_minutes;

                return $this->wrap(
                    'For '.Carbon::now()->format('l')." your scheduled session is {$name} ({$type}, {$dur} min). "
                    .($type === 'rest'
                        ? 'Treat it as recovery: light walk, mobility, and hit your protein target.'
                        : 'Warm up 5 minutes, follow your plan exercises, and prioritise form over speed.'),
                    ['What if I skip today?', 'Post-workout meal', 'Too tired to train'],
                );
            }

            return $this->wrap(
                'Check the Workouts section on your dashboard for your full 4-week schedule. If nothing appears, your workout preference may be nutrition-only — walking still helps on keto.',
                ['Walking after meals', 'Home vs gym', 'Plateau help'],
            );
        }

        if (preg_match('/\b(how many calories|calorie target|macros|protein target|carbs)\b/', $msg)) {
            if ($cal) {
                $plan = $user->dietPlan;

                return $this->wrap(
                    "Your personalised keto targets:\n\n"
                    ."• Calories: {$cal} kcal/day\n"
                    ."• Fat: {$plan->fat_grams}g ({$plan->fat_pct}%)\n"
                    ."• Protein: {$plan->protein_grams}g ({$plan->protein_pct}%)\n"
                    ."• Carbs: {$plan->carb_grams}g net-style ({$plan->carb_pct}%)\n\n"
                    .'These were calculated from your quiz (age, weight, activity, goal).',
                    ['Why so much fat?', 'Can I eat more protein?', 'Meal examples'],
                );
            }

            return $this->wrap(
                'Complete the onboarding quiz so I can calculate your macros from your stats. Until then I can only give general keto guidance.',
                ['Start quiz', 'What is keto?', 'Sample day of eating'],
            );
        }

        if (preg_match('/\b(lose weight|fat loss|weight loss|how fast)\b/', $msg)) {
            $weeks = $user->dietPlan?->estimated_weeks_to_goal;

            return $this->wrap(
                'Sustainable fat loss on keto usually shows up as 0.3–0.7 kg per week on average when you hit protein and stay near your calorie target. '
                .($weeks ? "Your plan estimates about {$weeks} weeks toward your goal weight — use weekly averages, not daily noise." : '')
                .($cal ? " Hold near {$cal} kcal/day unless 10–14 days of data say otherwise." : ''),
                ['Plateau for 2 weeks', 'Walking tips', 'Track progress how?'],
            );
        }

        // Keyword rules from existing coach (plateau, hydration, etc.)
        $keyword = $this->keywordCoach->respond($user, $message);

        return array_merge($keyword, ['engine' => 'prebuilt']);
    }

    /**
     * @param  array<int, string>  $chips
     * @return array{reply: string, chips: array<int, string>, engine: string}
     */
    protected function wrap(string $reply, array $chips): array
    {
        return [
            'reply' => $reply,
            'chips' => $chips,
            'engine' => 'prebuilt',
        ];
    }
}
