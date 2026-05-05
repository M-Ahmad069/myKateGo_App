<?php

namespace App\Models;

use App\Enums\MealType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealPlan extends Model
{
    protected $fillable = [
        'diet_plan_id',
        'user_id',
        'day_of_week',
        'meal_type',
        'meal_name',
        'description',
        'calories',
        'fat_g',
        'protein_g',
        'carb_g',
        'day_name',
        'prep_time_min',
        'ingredients',
    ];

    protected function casts(): array
    {
        return [
            'meal_type' => MealType::class,
            'ingredients' => 'array',
        ];
    }

    public function dietPlan(): BelongsTo
    {
        return $this->belongsTo(DietPlan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
