<?php

namespace App\Services;

use App\Enums\Gender;
use App\Enums\WorkoutLocation;
use App\Enums\WorkoutPreference;
use App\Enums\WorkoutType;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\AI\WorkoutPlanAI;
use Illuminate\Support\Facades\Log;

class WorkoutPlanService
{
    public function __construct(
        protected WorkoutPlanAI $workoutPlanAI,
    ) {}

    public function generate(User $user): void
    {
        if ($user->workout_preference === WorkoutPreference::None) {
            $user->workoutPlans()->delete();
            $this->fillRestOnlyWeeks($user);

            return;
        }

        $driver = (string) config('fitgo.workout_plan_driver', 'openai');

        if ($driver === 'openai') {
            try {
                $this->workoutPlanAI->generateFromOpenAI($user);

                return;
            } catch (\Throwable $e) {
                Log::warning('WorkoutPlanAI failed; using rule templates', ['error' => $e->getMessage()]);
            }
        }

        $this->generateFromRuleTemplates($user);
    }

    protected function generateFromRuleTemplates(User $user): void
    {
        $user->workoutPlans()->delete();

        $location = $this->resolveLocation($user->workout_preference);
        $template = $this->weekTemplate($user);
        $lib = $this->exerciseLibrary();

        for ($week = 1; $week <= 4; $week++) {
            foreach ($template as $dow => $entry) {
                $type = $entry['type'];
                $name = $entry['name'];
                $duration = $entry['minutes'];
                $key = match ($type) {
                    WorkoutType::Strength => 'strength',
                    WorkoutType::Cardio => 'cardio',
                    WorkoutType::Hiit => 'hiit',
                    WorkoutType::Rest => 'rest',
                    WorkoutType::Flexibility => 'flexibility',
                };
                $exercises = match ($type) {
                    WorkoutType::Rest => [['name' => 'Light walk', 'sets' => 1, 'duration_min' => 20]],
                    WorkoutType::Flexibility => $this->pickExercises($lib['flexibility'] ?? [], 6),
                    default => $this->pickExercises($lib[$key] ?? [], $type === WorkoutType::Hiit ? 6 : 8),
                };

                WorkoutPlan::create([
                    'user_id' => $user->id,
                    'week_number' => $week,
                    'day_of_week' => $dow,
                    'workout_type' => $type,
                    'workout_name' => $week > 1 ? $name.' — Week '.$week : $name,
                    'duration_minutes' => $duration,
                    'exercises' => $exercises,
                    'location' => $type === WorkoutType::Rest ? WorkoutLocation::Home : $location,
                ]);
            }
        }
    }

    protected function resolveLocation(WorkoutPreference $pref): WorkoutLocation
    {
        return match ($pref) {
            WorkoutPreference::Home => WorkoutLocation::Home,
            WorkoutPreference::Gym => WorkoutLocation::Gym,
            WorkoutPreference::Both => WorkoutLocation::Either,
            WorkoutPreference::None => WorkoutLocation::Home,
        };
    }

    /**
     * @return array<int, array{type: WorkoutType, name: string, minutes: int}>
     */
    protected function weekTemplate(User $user): array
    {
        $tags = $user->coaching_tags ?? [];
        $g = $user->gender;
        $spec = (string) ($user->gender_specific_data ?? '');

        if ($g === Gender::Female) {
            $lowEnergy = in_array('energy_low', $tags, true);
            $thyroid = $spec === 'thyroid' || in_array('female_horm_thyroid', $tags, true);
            $menopause = $spec === 'menopause' || in_array('female_horm_menopause', $tags, true);

            if ($spec === 'pcos' || in_array('female_horm_pcos', $tags, true)) {
                return $lowEnergy ? $this->femalePcosLowEnergyWeek() : $this->femalePcosWeek();
            }

            if ($thyroid || $menopause || $lowEnergy) {
                return $this->femaleGentleWeek();
            }

            if (in_array('cycle_irregular', $tags, true)) {
                return $this->femaleIrregularCycleWeek();
            }

            return $this->femaleGeneralWeek();
        }

        // Male — high stress / poor recovery → beginner-style volume
        if (in_array('recovery_high', $tags, true)) {
            return $this->maleBeginnerWeek();
        }

        if (in_array($spec, ['beginner'], true)) {
            return $this->maleBeginnerWeek();
        }

        if (in_array('focus_lose_fat', $tags, true)) {
            return $this->maleFatLossBiasWeek();
        }

        return $this->maleAdvancedWeek();
    }

    /**
     * Keys 1-7 = Mon-Sun (ISO).
     *
     * @return array<int, array{type: WorkoutType, name: string, minutes: int}>
     */
    protected function maleBeginnerWeek(): array
    {
        return [
            1 => ['type' => WorkoutType::Strength, 'name' => 'Upper Body Strength', 'minutes' => 45],
            2 => ['type' => WorkoutType::Cardio, 'name' => 'Low-Impact Cardio', 'minutes' => 35],
            3 => ['type' => WorkoutType::Rest, 'name' => 'Rest & Recovery', 'minutes' => 0],
            4 => ['type' => WorkoutType::Strength, 'name' => 'Lower Body Strength', 'minutes' => 50],
            5 => ['type' => WorkoutType::Cardio, 'name' => 'Steady-State Cardio', 'minutes' => 40],
            6 => ['type' => WorkoutType::Strength, 'name' => 'Full Body Basics', 'minutes' => 45],
            7 => ['type' => WorkoutType::Rest, 'name' => 'Active Recovery', 'minutes' => 0],
        ];
    }

    protected function maleAdvancedWeek(): array
    {
        return [
            1 => ['type' => WorkoutType::Strength, 'name' => 'Push Strength', 'minutes' => 55],
            2 => ['type' => WorkoutType::Strength, 'name' => 'Pull Strength', 'minutes' => 55],
            3 => ['type' => WorkoutType::Hiit, 'name' => 'HIIT Metabolic', 'minutes' => 30],
            4 => ['type' => WorkoutType::Strength, 'name' => 'Legs & Core', 'minutes' => 60],
            5 => ['type' => WorkoutType::Hiit, 'name' => 'HIIT Conditioning', 'minutes' => 28],
            6 => ['type' => WorkoutType::Strength, 'name' => 'Full Body Power', 'minutes' => 50],
            7 => ['type' => WorkoutType::Rest, 'name' => 'Rest Day', 'minutes' => 0],
        ];
    }

    protected function femaleGeneralWeek(): array
    {
        return [
            1 => ['type' => WorkoutType::Strength, 'name' => 'Total Body Strength', 'minutes' => 42],
            2 => ['type' => WorkoutType::Cardio, 'name' => 'Low-Impact Cardio', 'minutes' => 35],
            3 => ['type' => WorkoutType::Rest, 'name' => 'Rest & Stretch', 'minutes' => 0],
            4 => ['type' => WorkoutType::Strength, 'name' => 'Glutes & Core', 'minutes' => 45],
            5 => ['type' => WorkoutType::Cardio, 'name' => 'Incline Walking', 'minutes' => 40],
            6 => ['type' => WorkoutType::Strength, 'name' => 'Upper & Posture', 'minutes' => 40],
            7 => ['type' => WorkoutType::Rest, 'name' => 'Recovery Walk', 'minutes' => 0],
        ];
    }

    protected function femalePcosLowEnergyWeek(): array
    {
        return [
            1 => ['type' => WorkoutType::Strength, 'name' => 'Light resistance A', 'minutes' => 32],
            2 => ['type' => WorkoutType::Cardio, 'name' => 'Easy walking', 'minutes' => 35],
            3 => ['type' => WorkoutType::Rest, 'name' => 'Rest', 'minutes' => 0],
            4 => ['type' => WorkoutType::Strength, 'name' => 'Light resistance B', 'minutes' => 32],
            5 => ['type' => WorkoutType::Cardio, 'name' => 'Walking & mobility', 'minutes' => 38],
            6 => ['type' => WorkoutType::Flexibility, 'name' => 'Gentle mobility', 'minutes' => 25],
            7 => ['type' => WorkoutType::Rest, 'name' => 'Recovery', 'minutes' => 0],
        ];
    }

    protected function femaleGentleWeek(): array
    {
        return [
            1 => ['type' => WorkoutType::Strength, 'name' => 'Total body (light)', 'minutes' => 36],
            2 => ['type' => WorkoutType::Cardio, 'name' => 'Incline walk', 'minutes' => 35],
            3 => ['type' => WorkoutType::Rest, 'name' => 'Rest & stretch', 'minutes' => 0],
            4 => ['type' => WorkoutType::Strength, 'name' => 'Glutes & posture', 'minutes' => 38],
            5 => ['type' => WorkoutType::Cardio, 'name' => 'Outdoor walk', 'minutes' => 40],
            6 => ['type' => WorkoutType::Flexibility, 'name' => 'Yoga flow (easy)', 'minutes' => 28],
            7 => ['type' => WorkoutType::Rest, 'name' => 'Recovery walk', 'minutes' => 0],
        ];
    }

    protected function femaleIrregularCycleWeek(): array
    {
        return [
            1 => ['type' => WorkoutType::Strength, 'name' => 'Steady strength A', 'minutes' => 40],
            2 => ['type' => WorkoutType::Cardio, 'name' => 'Zone-2 walk', 'minutes' => 38],
            3 => ['type' => WorkoutType::Rest, 'name' => 'Rest', 'minutes' => 0],
            4 => ['type' => WorkoutType::Strength, 'name' => 'Steady strength B', 'minutes' => 42],
            5 => ['type' => WorkoutType::Cardio, 'name' => 'Low-impact cardio', 'minutes' => 35],
            6 => ['type' => WorkoutType::Strength, 'name' => 'Upper & core', 'minutes' => 36],
            7 => ['type' => WorkoutType::Rest, 'name' => 'Active recovery', 'minutes' => 0],
        ];
    }

    protected function maleFatLossBiasWeek(): array
    {
        return [
            1 => ['type' => WorkoutType::Strength, 'name' => 'Upper metabolic', 'minutes' => 48],
            2 => ['type' => WorkoutType::Cardio, 'name' => 'Steady cardio (fat loss)', 'minutes' => 42],
            3 => ['type' => WorkoutType::Rest, 'name' => 'Rest', 'minutes' => 0],
            4 => ['type' => WorkoutType::Strength, 'name' => 'Lower strength', 'minutes' => 50],
            5 => ['type' => WorkoutType::Cardio, 'name' => 'Incline walk / bike', 'minutes' => 40],
            6 => ['type' => WorkoutType::Hiit, 'name' => 'Short HIIT finisher', 'minutes' => 22],
            7 => ['type' => WorkoutType::Rest, 'name' => 'Rest', 'minutes' => 0],
        ];
    }

    protected function femalePcosWeek(): array
    {
        return [
            1 => ['type' => WorkoutType::Strength, 'name' => 'Resistance Training A', 'minutes' => 40],
            2 => ['type' => WorkoutType::Cardio, 'name' => 'Brisk Walking', 'minutes' => 45],
            3 => ['type' => WorkoutType::Rest, 'name' => 'Rest', 'minutes' => 0],
            4 => ['type' => WorkoutType::Strength, 'name' => 'Resistance Training B', 'minutes' => 40],
            5 => ['type' => WorkoutType::Cardio, 'name' => 'Walking & Mobility', 'minutes' => 40],
            6 => ['type' => WorkoutType::Strength, 'name' => 'Full Body Resistance', 'minutes' => 38],
            7 => ['type' => WorkoutType::Rest, 'name' => 'Gentle Recovery', 'minutes' => 0],
        ];
    }

    protected function fillRestOnlyWeeks(User $user): void
    {
        for ($week = 1; $week <= 4; $week++) {
            for ($dow = 1; $dow <= 7; $dow++) {
                WorkoutPlan::create([
                    'user_id' => $user->id,
                    'week_number' => $week,
                    'day_of_week' => $dow,
                    'workout_type' => WorkoutType::Rest,
                    'workout_name' => 'Rest — Nutrition Focus',
                    'duration_minutes' => 0,
                    'exercises' => [['name' => 'Optional light walk', 'sets' => 1, 'duration_min' => 15]],
                    'location' => WorkoutLocation::Home,
                ]);
            }
        }
    }

    /**
     * @param  array<int, array{name: string, sets?: int, reps?: string}>  $pool
     * @return array<int, mixed>
     */
    protected function pickExercises(array $pool, int $count): array
    {
        shuffle($pool);

        return array_slice(array_values($pool), 0, min($count, count($pool)));
    }

    /**
     * @return array<string, array<int, array{name: string, sets: int, reps: string}>>
     */
    protected function exerciseLibrary(): array
    {
        return [
            'strength' => [
                ['name' => 'Barbell bench press', 'sets' => 4, 'reps' => '8-10'],
                ['name' => 'Incline dumbbell press', 'sets' => 3, 'reps' => '10-12'],
                ['name' => 'Cable row', 'sets' => 4, 'reps' => '10-12'],
                ['name' => 'Lat pulldown', 'sets' => 3, 'reps' => '10-12'],
                ['name' => 'Overhead press', 'sets' => 4, 'reps' => '8-10'],
                ['name' => 'Goblet squat', 'sets' => 4, 'reps' => '10-12'],
                ['name' => 'Romanian deadlift', 'sets' => 4, 'reps' => '8-10'],
                ['name' => 'Leg press', 'sets' => 3, 'reps' => '12-15'],
                ['name' => 'Walking lunge', 'sets' => 3, 'reps' => '12 each'],
                ['name' => 'Hip thrust', 'sets' => 4, 'reps' => '10-12'],
                ['name' => 'Plank', 'sets' => 3, 'reps' => '45s'],
                ['name' => 'Pull-up / assisted pull-up', 'sets' => 3, 'reps' => 'AMRAP'],
                ['name' => 'Face pull', 'sets' => 3, 'reps' => '15-20'],
                ['name' => 'Bulgarian split squat', 'sets' => 3, 'reps' => '10 each'],
                ['name' => 'Chest-supported row', 'sets' => 3, 'reps' => '12'],
                ['name' => 'Leg curl', 'sets' => 3, 'reps' => '12-15'],
                ['name' => 'Calf raise', 'sets' => 4, 'reps' => '15-20'],
                ['name' => 'Side plank', 'sets' => 3, 'reps' => '30s each'],
                ['name' => 'Barbell row', 'sets' => 4, 'reps' => '8-10'],
                ['name' => 'Tricep pushdown', 'sets' => 3, 'reps' => '12-15'],
                ['name' => 'Bicep curl', 'sets' => 3, 'reps' => '12'],
                ['name' => 'Push-up', 'sets' => 4, 'reps' => '12-20'],
            ],
            'cardio' => [
                ['name' => 'Incline treadmill walk', 'sets' => 1, 'reps' => '35 min'],
                ['name' => 'Bike intervals steady', 'sets' => 1, 'reps' => '30 min'],
                ['name' => 'Elliptical cross-trainer', 'sets' => 1, 'reps' => '30 min'],
                ['name' => 'Rowing machine', 'sets' => 1, 'reps' => '25 min'],
                ['name' => 'Outdoor brisk walk', 'sets' => 1, 'reps' => '40 min'],
                ['name' => 'Swimming easy laps', 'sets' => 1, 'reps' => '30 min'],
                ['name' => 'Stair climber moderate', 'sets' => 1, 'reps' => '20 min'],
                ['name' => 'Jump rope easy pace', 'sets' => 5, 'reps' => '3 min'],
                ['name' => 'Cycle commute pace', 'sets' => 1, 'reps' => '35 min'],
                ['name' => 'Walking lunges + walk', 'sets' => 3, 'reps' => '10 min'],
                ['name' => 'Shadow boxing', 'sets' => 4, 'reps' => '3 min'],
                ['name' => 'Machine ski erg', 'sets' => 1, 'reps' => '20 min'],
                ['name' => 'Treadmill hike', 'sets' => 1, 'reps' => '35 min'],
                ['name' => 'Recumbent bike', 'sets' => 1, 'reps' => '30 min'],
                ['name' => 'Light jog', 'sets' => 1, 'reps' => '25 min'],
                ['name' => 'VersaClimber', 'sets' => 1, 'reps' => '15 min'],
                ['name' => 'Circuit walk stations', 'sets' => 4, 'reps' => '5 min'],
                ['name' => 'Deep breathing walk', 'sets' => 1, 'reps' => '40 min'],
                ['name' => 'Park trail walk', 'sets' => 1, 'reps' => '45 min'],
                ['name' => 'Cool-down stretch cardio', 'sets' => 1, 'reps' => '10 min'],
                ['name' => 'Tempo bike', 'sets' => 1, 'reps' => '28 min'],
            ],
            'flexibility' => [
                ['name' => 'Cat-cow flow', 'sets' => 2, 'reps' => '60s'],
                ['name' => 'Hip flexor stretch', 'sets' => 2, 'reps' => '45s each'],
                ['name' => 'Thoracic rotation', 'sets' => 2, 'reps' => '10 each'],
                ['name' => 'Hamstring strap stretch', 'sets' => 2, 'reps' => '60s each'],
                ['name' => 'Child pose breathing', 'sets' => 2, 'reps' => '90s'],
                ['name' => 'Shoulder CARs', 'sets' => 2, 'reps' => '5 each dir'],
                ['name' => 'Pigeon pose', 'sets' => 2, 'reps' => '60s each'],
                ['name' => 'World greatest stretch', 'sets' => 2, 'reps' => '5 each side'],
            ],
            'hiit' => [
                ['name' => 'Burpee', 'sets' => 5, 'reps' => '30s on'],
                ['name' => 'High knees', 'sets' => 5, 'reps' => '30s on'],
                ['name' => 'Mountain climber', 'sets' => 5, 'reps' => '30s on'],
                ['name' => 'Kettlebell swing', 'sets' => 6, 'reps' => '20s on'],
                ['name' => 'Battle ropes', 'sets' => 6, 'reps' => '20s on'],
                ['name' => 'Row sprint', 'sets' => 8, 'reps' => '30s on'],
                ['name' => 'Bike sprint', 'sets' => 8, 'reps' => '20s on'],
                ['name' => 'Box jump', 'sets' => 5, 'reps' => '8 reps'],
                ['name' => 'Skater hop', 'sets' => 5, 'reps' => '40s'],
                ['name' => 'Jump squat', 'sets' => 5, 'reps' => '12 reps'],
                ['name' => 'Speed rope doubles', 'sets' => 6, 'reps' => '30s'],
                ['name' => 'Assault bike sprint', 'sets' => 6, 'reps' => '15s on'],
                ['name' => 'Medicine ball slam', 'sets' => 5, 'reps' => '12'],
                ['name' => 'Tuck jump', 'sets' => 5, 'reps' => '10'],
                ['name' => 'Sled push', 'sets' => 4, 'reps' => '20m'],
                ['name' => 'Farmer carry run', 'sets' => 4, 'reps' => '30m'],
                ['name' => 'Plank jack', 'sets' => 4, 'reps' => '40s'],
                ['name' => 'Lateral bound', 'sets' => 4, 'reps' => '12'],
                ['name' => 'Thruster', 'sets' => 5, 'reps' => '10'],
                ['name' => 'Air bike tabata', 'sets' => 8, 'reps' => '20/10'],
                ['name' => 'Bear crawl', 'sets' => 4, 'reps' => '25m'],
                ['name' => 'Sprint intervals treadmill', 'sets' => 10, 'reps' => '20s on'],
            ],
        ];
    }
}
