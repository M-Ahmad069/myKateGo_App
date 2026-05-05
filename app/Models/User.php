<?php

namespace App\Models;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\Goal;
use App\Enums\WorkoutPreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'age',
        'height_cm',
        'weight_kg',
        'target_weight_kg',
        'activity_level',
        'workout_preference',
        'diet_restrictions',
        'gender_specific_data',
        'goal',
        'quiz_profile',
        'plan_segment',
        'coaching_tags',
        'plan_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'diet_restrictions' => 'array',
            'gender' => Gender::class,
            'goal' => Goal::class,
            'activity_level' => ActivityLevel::class,
            'workout_preference' => WorkoutPreference::class,
            'height_cm' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'target_weight_kg' => 'decimal:2',
            'quiz_profile' => 'array',
            'coaching_tags' => 'array',
        ];
    }

    public function dietPlan(): HasOne
    {
        return $this->hasOne(DietPlan::class);
    }

    public function mealPlans(): HasMany
    {
        return $this->hasMany(MealPlan::class);
    }

    public function workoutPlans(): HasMany
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    public function progressLogs(): HasMany
    {
        return $this->hasMany(ProgressLog::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }
}
