<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_meal_plan_id',
        'meal_type',
        'food_id',
        'food_name',
        'serving_size',
        'calories',
        'protein_g',
        'carbs_g',
        'fat_g',
        'order',
        'notes',
    ];

    protected $casts = [
        'serving_size' => 'decimal:2',
        'calories' => 'decimal:2',
        'protein_g' => 'decimal:2',
        'carbs_g' => 'decimal:2',
        'fat_g' => 'decimal:2',
    ];

    /**
     * Get the daily meal plan that owns the item
     */
    public function dailyMealPlan(): BelongsTo
    {
        return $this->belongsTo(DailyMealPlan::class);
    }

    /**
     * Get the food referenced by this item
     */
    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    /**
     * Scope to filter by meal type
     */
    public function scopeByMealType($query, string $mealType)
    {
        return $query->where('meal_type', $mealType);
    }

    /**
     * Scope to order by position
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
