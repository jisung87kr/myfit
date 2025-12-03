<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyMealPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'diet_plan_id',
        'day_number',
        'date',
        'total_calories',
        'total_protein_g',
        'total_carbs_g',
        'total_fat_g',
        'tips',
    ];

    protected $casts = [
        'date' => 'date',
        'total_calories' => 'decimal:2',
        'total_protein_g' => 'decimal:2',
        'total_carbs_g' => 'decimal:2',
        'total_fat_g' => 'decimal:2',
    ];

    /**
     * Get the diet plan that owns the daily meal plan
     */
    public function dietPlan(): BelongsTo
    {
        return $this->belongsTo(DietPlan::class);
    }

    /**
     * Get all meal items for this day
     */
    public function mealItems(): HasMany
    {
        return $this->hasMany(MealPlanItem::class);
    }

    /**
     * Get breakfast items
     */
    public function breakfast(): HasMany
    {
        return $this->mealItems()->where('meal_type', 'breakfast')->orderBy('order');
    }

    /**
     * Get lunch items
     */
    public function lunch(): HasMany
    {
        return $this->mealItems()->where('meal_type', 'lunch')->orderBy('order');
    }

    /**
     * Get dinner items
     */
    public function dinner(): HasMany
    {
        return $this->mealItems()->where('meal_type', 'dinner')->orderBy('order');
    }

    /**
     * Get snack items
     */
    public function snacks(): HasMany
    {
        return $this->mealItems()->where('meal_type', 'snack')->orderBy('order');
    }

    /**
     * Recalculate totals from meal items
     */
    public function recalculateTotals(): void
    {
        $totals = $this->mealItems()->selectRaw('
            SUM(calories) as total_calories,
            SUM(protein_g) as total_protein_g,
            SUM(carbs_g) as total_carbs_g,
            SUM(fat_g) as total_fat_g
        ')->first();

        $this->update([
            'total_calories' => $totals->total_calories ?? 0,
            'total_protein_g' => $totals->total_protein_g ?? 0,
            'total_carbs_g' => $totals->total_carbs_g ?? 0,
            'total_fat_g' => $totals->total_fat_g ?? 0,
        ]);
    }
}
