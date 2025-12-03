<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCalculation extends Model
{
    protected $fillable = [
        'user_id',
        'bmr',
        'tdee',
        'target_calories',
        'target_protein_g',
        'target_carbs_g',
        'target_fat_g',
        'calculated_at',
    ];

    protected $casts = [
        'bmr' => 'decimal:2',
        'tdee' => 'decimal:2',
        'target_calories' => 'decimal:2',
        'target_protein_g' => 'decimal:2',
        'target_carbs_g' => 'decimal:2',
        'target_fat_g' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    /**
     * 사용자 관계
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
