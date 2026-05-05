<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DietPlan extends Model
{
    protected $fillable = [
        'user_id',
        'daily_calories',
        'fat_pct',
        'protein_pct',
        'carb_pct',
        'fat_grams',
        'protein_grams',
        'carb_grams',
        'plan_type',
        'estimated_weeks_to_goal',
        'raw_plan',
        'plan_summary',
        'key_foods',
        'foods_to_avoid',
        'daily_tip',
    ];

    protected function casts(): array
    {
        return [
            'raw_plan' => 'array',
            'key_foods' => 'array',
            'foods_to_avoid' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mealPlans(): HasMany
    {
        return $this->hasMany(MealPlan::class);
    }
}
