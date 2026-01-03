<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    protected $table = 'foods';

    protected $fillable = [
        'name',
        'name_en',
        'category',
        'serving_size',
        'calories',
        'protein_g',
        'carbs_g',
        'fat_g',
        'fiber_g',
        'sodium_mg',
        'image_url',
    ];

    protected $casts = [
        'serving_size' => 'decimal:2',
        'calories' => 'decimal:2',
        'protein_g' => 'decimal:2',
        'carbs_g' => 'decimal:2',
        'fat_g' => 'decimal:2',
        'fiber_g' => 'decimal:2',
        'sodium_mg' => 'decimal:2',
    ];

    /**
     * 카테고리별 필터링
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * 이름으로 검색
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('name_en', 'like', "%{$search}%");
    }

    /**
     * Get meal logs using this food
     */
    public function mealLogs()
    {
        return $this->hasMany(\App\Models\MealLog::class);
    }

    /**
     * Get meal plan items using this food
     */
    public function mealPlanItems()
    {
        return $this->hasMany(\App\Models\MealPlanItem::class);
    }
}
