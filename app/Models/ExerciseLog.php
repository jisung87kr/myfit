<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'exercise_id',
        'exercise_name',
        'duration_minutes',
        'calories_burned',
        'intensity',
        'exercise_time',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'duration_minutes' => 'integer',
        'calories_burned' => 'decimal:2',
        'exercise_time' => 'datetime:H:i',
    ];

    /**
     * Get the user that owns the exercise log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the exercise referenced by this log
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
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
     * Scope to filter by intensity
     */
    public function scopeByIntensity($query, string $intensity)
    {
        return $query->where('intensity', $intensity);
    }

    /**
     * Get today's exercises for a user
     */
    public static function getTodaysExercises(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return static::forUser($userId)
            ->forDate(now()->toDateString())
            ->orderBy('exercise_time')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get daily summary for a user on a specific date
     */
    public static function getDailySummary(int $userId, string $date): array
    {
        $exercises = static::forUser($userId)
            ->forDate($date)
            ->get();

        return [
            'date' => $date,
            'total_duration_minutes' => $exercises->sum('duration_minutes'),
            'total_calories_burned' => $exercises->sum('calories_burned'),
            'exercise_count' => $exercises->count(),
            'exercises_by_intensity' => [
                '낮음' => $exercises->where('intensity', '낮음')->count(),
                '보통' => $exercises->where('intensity', '보통')->count(),
                '높음' => $exercises->where('intensity', '높음')->count(),
                '매우 높음' => $exercises->where('intensity', '매우 높음')->count(),
            ],
        ];
    }
}
