<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;

    protected $table = 'exercises';

    protected $fillable = [
        'name',
        'category',
        'intensity',
        'met_value',
        'calories_per_hour_per_kg',
        'description',
        'video_url',
    ];

    protected $casts = [
        'met_value' => 'decimal:2',
        'calories_per_hour_per_kg' => 'decimal:2',
    ];

    /**
     * 카테고리별 필터링
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * 강도별 필터링
     */
    public function scopeByIntensity($query, string $intensity)
    {
        return $query->where('intensity', $intensity);
    }

    /**
     * 이름으로 검색
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * 특정 체중과 시간에 대한 칼로리 계산
     */
    public function calculateCalories(float $weightKg, float $hours): float
    {
        return round($this->calories_per_hour_per_kg * $weightKg * $hours, 2);
    }

    /**
     * Get exercise logs using this exercise
     */
    public function exerciseLogs()
    {
        return $this->hasMany(\App\Models\ExerciseLog::class);
    }

    /**
     * Get daily exercise plans using this exercise
     */
    public function dailyExercisePlans()
    {
        return $this->hasMany(\App\Models\DailyExercisePlan::class);
    }
}
