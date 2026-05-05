<?php

namespace Database\Factories;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\Goal;
use App\Enums\WorkoutPreference;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $gender = fake()->randomElement([Gender::Male->value, Gender::Female->value]);
        $genderSpecific = $gender === Gender::Male->value
            ? fake()->randomElement(['beginner', 'some_exp', 'intermediate', 'advanced'])
            : fake()->randomElement(['none', 'pcos', 'thyroid', 'menopause']);

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'gender' => $gender,
            'age' => fake()->numberBetween(25, 55),
            'height_cm' => fake()->randomFloat(2, 160, 190),
            'weight_kg' => fake()->randomFloat(2, 60, 100),
            'target_weight_kg' => fake()->randomFloat(2, 55, 85),
            'activity_level' => fake()->randomElement(collect(ActivityLevel::cases())->map->value->all()),
            'workout_preference' => fake()->randomElement(collect(WorkoutPreference::cases())->map->value->all()),
            'diet_restrictions' => ['none'],
            'gender_specific_data' => $genderSpecific,
            'goal' => fake()->randomElement(collect(Goal::cases())->map->value->all()),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
