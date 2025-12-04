<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'meal_type',
        'food_id',
        'food_name',
        'serving_size',
        'calories',
        'protein_g',
        'carbs_g',
        'fat_g',
        'meal_time',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'serving_size' => 'decimal:2',
        'calories' => 'decimal:2',
        'protein_g' => 'decimal:2',
        'carbs_g' => 'decimal:2',
        'fat_g' => 'decimal:2',
        'meal_time' => 'datetime:H:i',
    ];

    /**
     * Get the user that owns the meal log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the food referenced by this log
     */
    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by date
     */
    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by meal type
     */
    public function scopeByMealType($query, string $mealType)
    {
        return $query->where('meal_type', $mealType);
    }

    /**
     * Get today's meals for a user
     */
    public static function getTodaysMeals(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return static::forUser($userId)
            ->forDate(now()->toDateString())
            ->orderBy('meal_time')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get daily summary for a user on a specific date
     */
    public static function getDailySummary(int $userId, string $date): array
    {
        $meals = static::forUser($userId)
            ->forDate($date)
            ->get();

        return [
            'date' => $date,
            'total_calories' => $meals->sum('calories'),
            'total_protein_g' => $meals->sum('protein_g'),
            'total_carbs_g' => $meals->sum('carbs_g'),
            'total_fat_g' => $meals->sum('fat_g'),
            'meal_count' => $meals->count(),
            'meals_by_type' => [
                'breakfast' => $meals->where('meal_type', 'breakfast')->count(),
                'lunch' => $meals->where('meal_type', 'lunch')->count(),
                'dinner' => $meals->where('meal_type', 'dinner')->count(),
                'snack' => $meals->where('meal_type', 'snack')->count(),
            ],
        ];
    }
}
