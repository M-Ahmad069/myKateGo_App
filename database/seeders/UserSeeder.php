<?php

namespace Database\Seeders;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\Goal;
use App\Enums\WorkoutPreference;
use App\Models\User;
use App\Services\DietPlanService;
use App\Services\WorkoutPlanService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'test@fitgo.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'gender' => Gender::Male,
                'age' => 32,
                'height_cm' => 178,
                'weight_kg' => 85,
                'target_weight_kg' => 78,
                'activity_level' => ActivityLevel::Moderate,
                'workout_preference' => WorkoutPreference::Both,
                'diet_restrictions' => ['none'],
                'gender_specific_data' => 'intermediate',
                'goal' => Goal::LoseWeight,
            ]
        );

        $user->dietPlan?->mealPlans()->delete();
        $user->dietPlan?->delete();
        $user->workoutPlans()->delete();

        app(DietPlanService::class)->generate($user);
        app(WorkoutPlanService::class)->generate($user);
    }
}
