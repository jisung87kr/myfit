<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExerciseLog;
use App\Models\MealLog;
use App\Models\WeightLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DailyDashboardController extends Controller
{
    /**
     * Get daily dashboard for a specific date
     */
    public function show(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $date = $request->date;
        $user = auth()->user();

        // Get meal summary
        $mealSummary = MealLog::getDailySummary($user->id, $date);

        // Get exercise summary
        $exerciseSummary = ExerciseLog::getDailySummary($user->id, $date);

        // Get weight log for this date
        $weightLog = WeightLog::forUser($user->id)
            ->forDate($date)
            ->first();

        // Get target calories from latest calculation
        $calculation = $user->calculations()->latest('calculated_at')->first();

        // Build dashboard data
        $dashboard = [
            'date' => $date,
            'nutrition' => [
                'calories_consumed' => $mealSummary['total_calories'],
                'protein_g' => $mealSummary['total_protein_g'],
                'carbs_g' => $mealSummary['total_carbs_g'],
                'fat_g' => $mealSummary['total_fat_g'],
                'meal_count' => $mealSummary['meal_count'],
                'meals_by_type' => $mealSummary['meals_by_type'],
            ],
            'exercise' => [
                'calories_burned' => $exerciseSummary['total_calories_burned'],
                'duration_minutes' => $exerciseSummary['total_duration_minutes'],
                'exercise_count' => $exerciseSummary['exercise_count'],
                'exercises_by_intensity' => $exerciseSummary['exercises_by_intensity'],
            ],
            'weight' => $weightLog ? [
                'current_weight' => $weightLog->weight,
                'notes' => $weightLog->notes,
            ] : null,
        ];

        // Add calorie balance calculations if target is available
        if ($calculation) {
            $targetCalories = $calculation->target_calories;
            $netCalories = $mealSummary['total_calories'] - $exerciseSummary['total_calories_burned'];

            $dashboard['calorie_balance'] = [
                'target_calories' => $targetCalories,
                'calories_consumed' => $mealSummary['total_calories'],
                'calories_burned' => $exerciseSummary['total_calories_burned'],
                'net_calories' => round($netCalories, 2),
                'remaining_calories' => round($targetCalories - $netCalories, 2),
                'percentage_of_target' => $targetCalories > 0
                    ? round(($netCalories / $targetCalories) * 100, 1)
                    : 0,
            ];
        }

        return response()->success('Daily dashboard retrieved successfully', $dashboard);
    }

    /**
     * Get today's dashboard
     */
    public function today(): JsonResponse
    {
        $date = now()->format('Y-m-d');
        $user = auth()->user();

        // Get meal summary
        $mealSummary = MealLog::getDailySummary($user->id, $date);

        // Get exercise summary
        $exerciseSummary = ExerciseLog::getDailySummary($user->id, $date);

        // Get latest weight
        $latestWeight = WeightLog::getLatestWeight($user->id);

        // Get target calories from latest calculation
        $calculation = $user->calculations()->latest('calculated_at')->first();

        // Build dashboard data
        $dashboard = [
            'date' => $date,
            'nutrition' => [
                'calories_consumed' => $mealSummary['total_calories'],
                'protein_g' => $mealSummary['total_protein_g'],
                'carbs_g' => $mealSummary['total_carbs_g'],
                'fat_g' => $mealSummary['total_fat_g'],
                'meal_count' => $mealSummary['meal_count'],
                'meals_by_type' => $mealSummary['meals_by_type'],
            ],
            'exercise' => [
                'calories_burned' => $exerciseSummary['total_calories_burned'],
                'duration_minutes' => $exerciseSummary['total_duration_minutes'],
                'exercise_count' => $exerciseSummary['exercise_count'],
                'exercises_by_intensity' => $exerciseSummary['exercises_by_intensity'],
            ],
            'weight' => $latestWeight ? [
                'current_weight' => $latestWeight->weight,
                'last_updated' => $latestWeight->date->format('Y-m-d'),
            ] : null,
        ];

        // Add calorie balance calculations if target is available
        if ($calculation) {
            $targetCalories = $calculation->target_calories;
            $netCalories = $mealSummary['total_calories'] - $exerciseSummary['total_calories_burned'];

            $dashboard['calorie_balance'] = [
                'target_calories' => $targetCalories,
                'calories_consumed' => $mealSummary['total_calories'],
                'calories_burned' => $exerciseSummary['total_calories_burned'],
                'net_calories' => round($netCalories, 2),
                'remaining_calories' => round($targetCalories - $netCalories, 2),
                'percentage_of_target' => $targetCalories > 0
                    ? round(($netCalories / $targetCalories) * 100, 1)
                    : 0,
            ];
        }

        return response()->success("Today's dashboard retrieved successfully", $dashboard);
    }

    /**
     * Get weekly summary
     */
    public function weeklySummary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $startDate = $request->start_date ?? now()->startOfWeek()->format('Y-m-d');
        $endDate = now()->parse($startDate)->endOfWeek()->format('Y-m-d');

        $user = auth()->user();

        // Get meals for the week
        $meals = MealLog::forUser($user->id)
            ->betweenDates($startDate, $endDate)
            ->get();

        // Get exercises for the week
        $exercises = ExerciseLog::forUser($user->id)
            ->betweenDates($startDate, $endDate)
            ->get();

        // Get weights for the week
        $weights = WeightLog::forUser($user->id)
            ->betweenDates($startDate, $endDate)
            ->orderBy('date', 'asc')
            ->get();

        // Calculate weekly totals
        $weeklySummary = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'nutrition' => [
                'total_calories' => $meals->sum('calories'),
                'total_protein_g' => $meals->sum('protein_g'),
                'total_carbs_g' => $meals->sum('carbs_g'),
                'total_fat_g' => $meals->sum('fat_g'),
                'daily_average_calories' => $meals->count() > 0
                    ? round($meals->sum('calories') / 7, 2)
                    : 0,
                'meal_count' => $meals->count(),
            ],
            'exercise' => [
                'total_calories_burned' => $exercises->sum('calories_burned'),
                'total_duration_minutes' => $exercises->sum('duration_minutes'),
                'daily_average_calories_burned' => $exercises->count() > 0
                    ? round($exercises->sum('calories_burned') / 7, 2)
                    : 0,
                'exercise_count' => $exercises->count(),
            ],
            'weight' => [
                'entry_count' => $weights->count(),
                'start_weight' => $weights->first()?->weight,
                'end_weight' => $weights->last()?->weight,
                'weight_change' => $weights->count() >= 2
                    ? round($weights->last()->weight - $weights->first()->weight, 2)
                    : 0,
            ],
        ];

        // Add daily breakdown
        $dailyBreakdown = [];
        $currentDate = now()->parse($startDate);

        for ($i = 0; $i < 7; $i++) {
            $date = $currentDate->format('Y-m-d');

            $dayMeals = $meals->filter(fn($m) => $m->date->format('Y-m-d') === $date);
            $dayExercises = $exercises->filter(fn($e) => $e->date->format('Y-m-d') === $date);
            $dayWeight = $weights->firstWhere('date.date', $date);

            $dailyBreakdown[] = [
                'date' => $date,
                'day_of_week' => $currentDate->format('l'),
                'calories_consumed' => $dayMeals->sum('calories'),
                'calories_burned' => $dayExercises->sum('calories_burned'),
                'net_calories' => $dayMeals->sum('calories') - $dayExercises->sum('calories_burned'),
                'weight' => $dayWeight?->weight,
            ];

            $currentDate->addDay();
        }

        $weeklySummary['daily_breakdown'] = $dailyBreakdown;

        return response()->success('Weekly summary retrieved successfully', $weeklySummary);
    }

    /**
     * Get quick stats
     */
    public function quickStats(): JsonResponse
    {
        $user = auth()->user();
        $today = now()->format('Y-m-d');

        // Get today's data
        $todayMeals = MealLog::forUser($user->id)->forDate($today)->sum('calories');
        $todayExercises = ExerciseLog::forUser($user->id)->forDate($today)->sum('calories_burned');

        // Get current weight
        $latestWeight = WeightLog::getLatestWeight($user->id);

        // Get current streak (consecutive days with logged meals)
        $streak = $this->calculateMealLoggingStreak($user->id);

        // Get total entries
        $totalMeals = MealLog::forUser($user->id)->count();
        $totalExercises = ExerciseLog::forUser($user->id)->count();
        $totalWeights = WeightLog::forUser($user->id)->count();

        $stats = [
            'today' => [
                'calories_consumed' => $todayMeals,
                'calories_burned' => $todayExercises,
                'net_calories' => $todayMeals - $todayExercises,
            ],
            'current_weight' => $latestWeight?->weight,
            'logging_streak_days' => $streak,
            'total_entries' => [
                'meals' => $totalMeals,
                'exercises' => $totalExercises,
                'weights' => $totalWeights,
            ],
        ];

        return response()->success('Quick stats retrieved successfully', $stats);
    }

    /**
     * Get monthly summary
     */
    public function monthlySummary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'year' => 'nullable|integer|min:2020|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $year = $request->year ?? now()->year;
        $month = $request->month ?? now()->month;

        $startDate = now()->setDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endDate = now()->setDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
        $daysInMonth = now()->setDate($year, $month, 1)->daysInMonth;

        $user = auth()->user();

        // Get data for the month
        $meals = MealLog::forUser($user->id)->betweenDates($startDate, $endDate)->get();
        $exercises = ExerciseLog::forUser($user->id)->betweenDates($startDate, $endDate)->get();
        $weights = WeightLog::forUser($user->id)->betweenDates($startDate, $endDate)->orderBy('date', 'asc')->get();

        // Calculate days with logs
        $daysWithMeals = $meals->groupBy(fn($m) => $m->date->format('Y-m-d'))->count();
        $daysWithExercises = $exercises->groupBy(fn($e) => $e->date->format('Y-m-d'))->count();
        $daysWithWeights = $weights->groupBy(fn($w) => $w->date->format('Y-m-d'))->count();

        $monthlySummary = [
            'period' => [
                'year' => $year,
                'month' => $month,
                'month_name' => now()->setDate($year, $month, 1)->format('F'),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days_in_month' => $daysInMonth,
            ],
            'nutrition' => [
                'total_calories' => round($meals->sum('calories'), 2),
                'total_protein_g' => round($meals->sum('protein_g'), 2),
                'total_carbs_g' => round($meals->sum('carbs_g'), 2),
                'total_fat_g' => round($meals->sum('fat_g'), 2),
                'daily_average_calories' => $daysWithMeals > 0
                    ? round($meals->sum('calories') / $daysWithMeals, 2)
                    : 0,
                'meal_count' => $meals->count(),
                'days_logged' => $daysWithMeals,
                'logging_rate' => round(($daysWithMeals / $daysInMonth) * 100, 1),
            ],
            'exercise' => [
                'total_calories_burned' => round($exercises->sum('calories_burned'), 2),
                'total_duration_minutes' => $exercises->sum('duration_minutes'),
                'daily_average_calories_burned' => $daysWithExercises > 0
                    ? round($exercises->sum('calories_burned') / $daysWithExercises, 2)
                    : 0,
                'exercise_count' => $exercises->count(),
                'days_logged' => $daysWithExercises,
                'logging_rate' => round(($daysWithExercises / $daysInMonth) * 100, 1),
            ],
            'weight' => [
                'entry_count' => $weights->count(),
                'start_weight' => $weights->first()?->weight,
                'end_weight' => $weights->last()?->weight,
                'min_weight' => $weights->min('weight'),
                'max_weight' => $weights->max('weight'),
                'avg_weight' => $weights->count() > 0 ? round($weights->avg('weight'), 2) : null,
                'weight_change' => $weights->count() >= 2
                    ? round($weights->last()->weight - $weights->first()->weight, 2)
                    : 0,
                'days_logged' => $daysWithWeights,
                'logging_rate' => round(($daysWithWeights / $daysInMonth) * 100, 1),
            ],
            'streaks' => $this->getStreakStats($user->id),
        ];

        // Weekly breakdown within the month
        $weeklyBreakdown = [];
        $currentDate = now()->parse($startDate)->startOfWeek();

        while ($currentDate->format('Y-m-d') <= $endDate) {
            $weekStart = max($currentDate->format('Y-m-d'), $startDate);
            $weekEnd = min($currentDate->copy()->endOfWeek()->format('Y-m-d'), $endDate);

            $weekMeals = $meals->filter(fn($m) => $m->date->format('Y-m-d') >= $weekStart && $m->date->format('Y-m-d') <= $weekEnd);
            $weekExercises = $exercises->filter(fn($e) => $e->date->format('Y-m-d') >= $weekStart && $e->date->format('Y-m-d') <= $weekEnd);

            $weeklyBreakdown[] = [
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'calories_consumed' => round($weekMeals->sum('calories'), 2),
                'calories_burned' => round($weekExercises->sum('calories_burned'), 2),
                'net_calories' => round($weekMeals->sum('calories') - $weekExercises->sum('calories_burned'), 2),
                'meal_count' => $weekMeals->count(),
                'exercise_count' => $weekExercises->count(),
            ];

            $currentDate->addWeek();
        }

        $monthlySummary['weekly_breakdown'] = $weeklyBreakdown;

        return response()->success('Monthly summary retrieved successfully', $monthlySummary);
    }

    /**
     * Get all streak statistics
     */
    public function streaks(): JsonResponse
    {
        $user = auth()->user();
        $stats = $this->getStreakStats($user->id);

        return response()->success('Streak statistics retrieved successfully', $stats);
    }

    /**
     * Get streak statistics for a user
     */
    private function getStreakStats(int $userId): array
    {
        return [
            'meal_logging' => [
                'current' => $this->calculateMealLoggingStreak($userId),
                'longest' => $this->calculateLongestStreak($userId, 'meals'),
            ],
            'exercise_logging' => [
                'current' => $this->calculateExerciseLoggingStreak($userId),
                'longest' => $this->calculateLongestStreak($userId, 'exercises'),
            ],
            'weight_logging' => [
                'current' => $this->calculateWeightLoggingStreak($userId),
                'longest' => $this->calculateLongestStreak($userId, 'weights'),
            ],
        ];
    }

    /**
     * Calculate meal logging streak
     */
    private function calculateMealLoggingStreak(int $userId): int
    {
        return $this->calculateCurrentStreak($userId, 'meals');
    }

    /**
     * Calculate exercise logging streak
     */
    private function calculateExerciseLoggingStreak(int $userId): int
    {
        return $this->calculateCurrentStreak($userId, 'exercises');
    }

    /**
     * Calculate weight logging streak
     */
    private function calculateWeightLoggingStreak(int $userId): int
    {
        return $this->calculateCurrentStreak($userId, 'weights');
    }

    /**
     * Calculate current streak for a specific type
     */
    private function calculateCurrentStreak(int $userId, string $type): int
    {
        $streak = 0;
        $currentDate = now();

        while (true) {
            $date = $currentDate->format('Y-m-d');
            $hasEntry = match ($type) {
                'meals' => MealLog::forUser($userId)->forDate($date)->exists(),
                'exercises' => ExerciseLog::forUser($userId)->forDate($date)->exists(),
                'weights' => WeightLog::forUser($userId)->forDate($date)->exists(),
                default => false,
            };

            if (!$hasEntry) {
                break;
            }

            $streak++;
            $currentDate->subDay();

            if ($streak >= 365) {
                break;
            }
        }

        return $streak;
    }

    /**
     * Calculate longest streak for a specific type
     */
    private function calculateLongestStreak(int $userId, string $type): int
    {
        $dates = match ($type) {
            'meals' => MealLog::forUser($userId)->distinct('date')->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->sort()->values(),
            'exercises' => ExerciseLog::forUser($userId)->distinct('date')->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->sort()->values(),
            'weights' => WeightLog::forUser($userId)->distinct('date')->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->sort()->values(),
            default => collect(),
        };

        if ($dates->isEmpty()) {
            return 0;
        }

        $longestStreak = 1;
        $currentStreak = 1;

        for ($i = 1; $i < $dates->count(); $i++) {
            $prevDate = now()->parse($dates[$i - 1]);
            $currDate = now()->parse($dates[$i]);

            if ($prevDate->diffInDays($currDate) === 1) {
                $currentStreak++;
                $longestStreak = max($longestStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }

        return $longestStreak;
    }
}
