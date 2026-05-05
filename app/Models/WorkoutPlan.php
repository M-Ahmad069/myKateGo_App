<?php

namespace App\Models;

use App\Enums\WorkoutLocation;
use App\Enums\WorkoutType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutPlan extends Model
{
    protected $fillable = [
        'user_id',
        'week_number',
        'day_of_week',
        'workout_type',
        'workout_name',
        'duration_minutes',
        'exercises',
        'location',
        'day_name',
        'warm_up',
        'cool_down',
        'intensity',
        'calories_burned_estimate',
    ];

    protected function casts(): array
    {
        return [
            'exercises' => 'array',
            'warm_up' => 'array',
            'cool_down' => 'array',
            'workout_type' => WorkoutType::class,
            'location' => WorkoutLocation::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
