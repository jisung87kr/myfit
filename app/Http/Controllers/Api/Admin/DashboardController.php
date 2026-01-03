<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\DietPlan;
use App\Models\ExerciseLog;
use App\Models\MealLog;
use App\Models\Post;
use App\Models\User;
use App\Models\WeightLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function index(): JsonResponse
    {
        $stats = [
            'users' => $this->getUserStats(),
            'activity' => $this->getActivityStats(),
            'diet_plans' => $this->getDietPlanStats(),
            'community' => $this->getCommunityStats(),
        ];

        return response()->success($stats, 'Dashboard statistics retrieved');
    }

    /**
     * Get user statistics
     */
    public function userStats(): JsonResponse
    {
        $stats = $this->getUserStats();
        $stats['growth'] = $this->getUserGrowth();

        return response()->success($stats, 'User statistics retrieved');
    }

    /**
     * Get activity statistics
     */
    public function activityStats(): JsonResponse
    {
        $stats = $this->getActivityStats();
        $stats['trends'] = $this->getActivityTrends();

        return response()->success($stats, 'Activity statistics retrieved');
    }

    private function getUserStats(): array
    {
        $now = now();

        return [
            'total' => User::count(),
            'today' => User::whereDate('created_at', $now->toDateString())->count(),
            'this_week' => User::where('created_at', '>=', $now->startOfWeek())->count(),
            'this_month' => User::where('created_at', '>=', $now->startOfMonth())->count(),
            'active_today' => User::whereHas('tokens', function ($q) use ($now) {
                $q->whereDate('last_used_at', $now->toDateString());
            })->count(),
        ];
    }

    private function getUserGrowth(): array
    {
        $growth = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $growth[] = [
                'date' => $date->format('Y-m-d'),
                'count' => User::whereDate('created_at', $date->toDateString())->count(),
            ];
        }
        return $growth;
    }

    private function getActivityStats(): array
    {
        $today = now()->toDateString();

        return [
            'meal_logs_today' => MealLog::whereDate('created_at', $today)->count(),
            'exercise_logs_today' => ExerciseLog::whereDate('created_at', $today)->count(),
            'weight_logs_today' => WeightLog::whereDate('created_at', $today)->count(),
            'total_meal_logs' => MealLog::count(),
            'total_exercise_logs' => ExerciseLog::count(),
            'total_weight_logs' => WeightLog::count(),
        ];
    }

    private function getActivityTrends(): array
    {
        $trends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $trends[] = [
                'date' => $date,
                'meals' => MealLog::whereDate('created_at', $date)->count(),
                'exercises' => ExerciseLog::whereDate('created_at', $date)->count(),
                'weights' => WeightLog::whereDate('created_at', $date)->count(),
            ];
        }
        return $trends;
    }

    private function getDietPlanStats(): array
    {
        return [
            'total' => DietPlan::count(),
            'active' => DietPlan::where('status', 'active')->count(),
            'generating' => DietPlan::where('status', 'generating')->count(),
            'completed' => DietPlan::where('status', 'completed')->count(),
            'failed' => DietPlan::where('status', 'failed')->count(),
            'success_rate' => $this->calculateSuccessRate(),
        ];
    }

    private function calculateSuccessRate(): float
    {
        $total = DietPlan::whereIn('status', ['active', 'completed', 'failed'])->count();
        if ($total === 0) {
            return 0;
        }
        $successful = DietPlan::whereIn('status', ['active', 'completed'])->count();
        return round(($successful / $total) * 100, 2);
    }

    private function getCommunityStats(): array
    {
        return [
            'total_posts' => Post::count(),
            'posts_today' => Post::whereDate('created_at', now()->toDateString())->count(),
            'active_challenges' => Challenge::where('status', 'ongoing')->count(),
            'total_challenge_participants' => DB::table('challenge_participants')
                ->where('status', 'active')
                ->count(),
        ];
    }
}
