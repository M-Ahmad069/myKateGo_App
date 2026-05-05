<?php

namespace App\Services\AI;

use App\Enums\WorkoutLocation;
use App\Enums\WorkoutPreference;
use App\Enums\WorkoutType;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Support\AiJson;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class WorkoutPlanAI
{
    /**
     * Replace user's workout plans with OpenAI-generated 4-week schedule.
     *
     * @throws \Throwable
     */
    public function generateFromOpenAI(User $user): void
    {
        if ($user->workout_preference === WorkoutPreference::None) {
            return;
        }

        $user->workoutPlans()->delete();

        $ctx = $user->quiz_profile ? json_encode($user->quiz_profile, JSON_UNESCAPED_UNICODE) : 'none';
        $tags = implode(', ', $user->coaching_tags ?? []);

        $prompt = <<<PROMPT
You are a certified personal trainer. Return ONLY valid JSON (no markdown).

Create a 4-week progressive workout plan (Mon–Sun) for this user:
- Gender: {$user->gender->value}
- Age: {$user->age}
- Goal: {$user->goal->value}
- Activity level: {$user->activity_level->value}
- Workout location preference: {$user->workout_preference->value}
- Fitness / health signal: {$user->gender_specific_data}
- Coaching tags: {$tags}
- Quiz profile: {$ctx}

Rules:
- 4 weeks, each week has 7 days (day 1 = Monday ... day 7 = Sunday)
- workout_type one of: strength, cardio, hiit, rest, flexibility
- location one of: home, gym, either
- No more than 2 consecutive high-intensity days
- Each training day: duration_minutes, exercises array of {name, sets, reps (string), rest_seconds (int), notes (string)}
- warm_up and cool_down: string arrays
- rest days: short name, duration 0, empty exercises or light walk
- Week 4 slightly easier (deload) than week 3

JSON shape:
{
  "plan_overview": "2 sentences",
  "weeks": [
    {
      "week": 1,
      "theme": "Foundation",
      "days": [
        {
          "day": 1,
          "day_name": "Monday",
          "workout_type": "strength",
          "workout_name": "Session name",
          "duration_minutes": 45,
          "location": "home",
          "intensity": "moderate",
          "calories_burned_estimate": 250,
          "warm_up": ["..."],
          "exercises": [{"name":"...","sets":3,"reps":"10-12","rest_seconds":60,"notes":""}],
          "cool_down": ["..."]
        }
      ]
    }
  ]
}
PROMPT;

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'max_tokens' => 6000,
            'temperature' => 0.55,
            'messages' => [
                ['role' => 'system', 'content' => 'Return only valid JSON. No code fences.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $raw = $response->choices[0]->message->content ?? '';
        $raw = AiJson::stripFences($raw);
        $data = json_decode($raw, true);

        if (! is_array($data) || empty($data['weeks']) || ! is_array($data['weeks'])) {
            throw new \RuntimeException('Workout AI: missing weeks');
        }

        $count = 0;
        foreach ($data['weeks'] as $week) {
            $weekNum = (int) ($week['week'] ?? 0);
            if ($weekNum < 1 || $weekNum > 4) {
                continue;
            }
            foreach ($week['days'] ?? [] as $day) {
                $dow = (int) ($day['day'] ?? 0);
                if ($dow < 1 || $dow > 7) {
                    continue;
                }
                $type = $this->mapWorkoutType((string) ($day['workout_type'] ?? 'strength'));
                $location = $this->mapLocation((string) ($day['location'] ?? 'home'));
                $exercises = $day['exercises'] ?? [];
                if (! is_array($exercises)) {
                    $exercises = [];
                }

                WorkoutPlan::create([
                    'user_id' => $user->id,
                    'week_number' => $weekNum,
                    'day_of_week' => $dow,
                    'day_name' => (string) ($day['day_name'] ?? ''),
                    'workout_type' => $type,
                    'workout_name' => (string) ($day['workout_name'] ?? 'Workout'),
                    'duration_minutes' => (int) ($day['duration_minutes'] ?? 0),
                    'intensity' => isset($day['intensity']) ? (string) $day['intensity'] : null,
                    'calories_burned_estimate' => isset($day['calories_burned_estimate']) ? (int) $day['calories_burned_estimate'] : null,
                    'exercises' => $exercises,
                    'warm_up' => is_array($day['warm_up'] ?? null) ? $day['warm_up'] : null,
                    'cool_down' => is_array($day['cool_down'] ?? null) ? $day['cool_down'] : null,
                    'location' => $type === WorkoutType::Rest ? WorkoutLocation::Home : $location,
                ]);
                $count++;
            }
        }

        if ($count === 0) {
            throw new \RuntimeException('Workout AI: zero rows saved');
        }

        Log::info('WorkoutPlanAI saved rows', ['user_id' => $user->id, 'rows' => $count]);
    }

    protected function mapWorkoutType(string $raw): WorkoutType
    {
        return match (strtolower($raw)) {
            'cardio' => WorkoutType::Cardio,
            'hiit' => WorkoutType::Hiit,
            'rest', 'recovery', 'active recovery' => WorkoutType::Rest,
            'flexibility', 'yoga', 'mobility', 'stretch' => WorkoutType::Flexibility,
            default => WorkoutType::Strength,
        };
    }

    protected function mapLocation(string $raw): WorkoutLocation
    {
        return match (strtolower(trim($raw))) {
            'gym' => WorkoutLocation::Gym,
            'either', 'both', 'mix', 'mixed' => WorkoutLocation::Either,
            default => WorkoutLocation::Home,
        };
    }
}
