<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeightLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'weight',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'weight' => 'decimal:2',
    ];

    /**
     * Get the user that owns the weight log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * Get latest weight for a user
     */
    public static function getLatestWeight(int $userId): ?self
    {
        return static::forUser($userId)
            ->orderBy('date', 'desc')
            ->first();
    }

    /**
     * Get weight history for a user
     */
    public static function getHistory(int $userId, int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        $startDate = now()->subDays($days)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        return static::forUser($userId)
            ->betweenDates($startDate, $endDate)
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get weight progress (change from start to latest)
     */
    public static function getProgress(int $userId): array
    {
        $logs = static::forUser($userId)
            ->orderBy('date', 'asc')
            ->get();

        if ($logs->isEmpty()) {
            return [
                'has_data' => false,
            ];
        }

        $firstLog = $logs->first();
        $latestLog = $logs->last();

        $weightChange = $latestLog->weight - $firstLog->weight;
        $percentageChange = $firstLog->weight > 0
            ? round(($weightChange / $firstLog->weight) * 100, 2)
            : 0;

        return [
            'has_data' => true,
            'start_date' => $firstLog->date->format('Y-m-d'),
            'start_weight' => $firstLog->weight,
            'latest_date' => $latestLog->date->format('Y-m-d'),
            'latest_weight' => $latestLog->weight,
            'weight_change' => round($weightChange, 2),
            'percentage_change' => $percentageChange,
            'total_days' => $firstLog->date->diffInDays($latestLog->date),
            'total_entries' => $logs->count(),
        ];
    }

    /**
     * Get statistics for a date range
     */
    public static function getStatistics(int $userId, string $startDate, string $endDate): array
    {
        $logs = static::forUser($userId)
            ->betweenDates($startDate, $endDate)
            ->orderBy('date', 'asc')
            ->get();

        if ($logs->isEmpty()) {
            return [
                'has_data' => false,
            ];
        }

        return [
            'has_data' => true,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'min_weight' => $logs->min('weight'),
            'max_weight' => $logs->max('weight'),
            'avg_weight' => round($logs->avg('weight'), 2),
            'current_weight' => $logs->last()->weight,
            'entry_count' => $logs->count(),
        ];
    }
}
