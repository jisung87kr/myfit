<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyExercisePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'diet_plan_id',
        'day_number',
        'date',
        'exercise_id',
        'exercise_name',
        'duration_minutes',
        'estimated_calories_burned',
        'intensity',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'estimated_calories_burned' => 'decimal:2',
    ];

    /**
     * Get the diet plan that owns the exercise plan
     */
    public function dietPlan(): BelongsTo
    {
        return $this->belongsTo(DietPlan::class);
    }

    /**
     * Get the exercise referenced by this plan
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /**
     * Scope to filter by day number
     */
    public function scopeByDay($query, int $dayNumber)
    {
        return $query->where('day_number', $dayNumber);
    }

    /**
     * Scope to filter by intensity
     */
    public function scopeByIntensity($query, string $intensity)
    {
        return $query->where('intensity', $intensity);
    }
}
