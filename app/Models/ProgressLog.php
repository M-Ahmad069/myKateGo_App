<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressLog extends Model
{
    protected $fillable = [
        'user_id',
        'logged_date',
        'weight_kg',
        'water_liters',
        'steps',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'logged_date' => 'date',
            'weight_kg' => 'decimal:2',
            'water_liters' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
