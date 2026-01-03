<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\ExerciseLog;
use App\Models\MealLog;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\WeightLog;
use Illuminate\Support\Collection;

class BadgeService
{
    /**
     * Check and award badges for a user
     */
    public function checkAndAwardBadges(User $user): Collection
    {
        $newBadges = collect();

        $badges = Badge::active()->get();

        foreach ($badges as $badge) {
            if ($this->userHasBadge($user, $badge)) {
                continue;
            }

            $progress = $this->calculateProgress($user, $badge);

            if ($progress >= $badge->requirement_value) {
                $this->awardBadge($user, $badge, $progress);
                $newBadges->push($badge);
            }
        }

        return $newBadges;
    }

    /**
     * Check if user already has a badge
     */
    public function userHasBadge(User $user, Badge $badge): bool
    {
        return UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->exists();
    }

    /**
     * Calculate progress for a badge
     */
    public function calculateProgress(User $user, Badge $badge): int
    {
        return match ($badge->category) {
            'streak' => $this->calculateStreakProgress($user, $badge),
            'meal' => $this->calculateMealProgress($user, $badge),
            'exercise' => $this->calculateExerciseProgress($user, $badge),
            'weight' => $this->calculateWeightProgress($user, $badge),
            'milestone' => $this->calculateMilestoneProgress($user, $badge),
            default => 0,
        };
    }

    /**
     * Award a badge to user
     */
    public function awardBadge(User $user, Badge $badge, int $progressValue = 0): UserBadge
    {
        return UserBadge::create([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'earned_at' => now(),
            'progress_value' => $progressValue,
        ]);
    }

    /**
     * Get all badges with user progress
     */
    public function getBadgesWithProgress(User $user): Collection
    {
        $badges = Badge::active()->get();
        $earnedBadgeIds = $user->earnedBadges()->pluck('badge_id')->toArray();

        return $badges->map(function ($badge) use ($user, $earnedBadgeIds) {
            $isEarned = in_array($badge->id, $earnedBadgeIds);
            $progress = $isEarned ? $badge->requirement_value : $this->calculateProgress($user, $badge);

            return [
                'id' => $badge->id,
                'code' => $badge->code,
                'name' => $badge->name,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'category' => $badge->category,
                'type' => $badge->type,
                'requirement_value' => $badge->requirement_value,
                'requirement_type' => $badge->requirement_type,
                'is_earned' => $isEarned,
                'progress' => $progress,
                'progress_percentage' => min(100, round(($progress / $badge->requirement_value) * 100, 1)),
                'earned_at' => $isEarned
                    ? $user->earnedBadges()->where('badge_id', $badge->id)->first()?->earned_at
                    : null,
            ];
        });
    }

    /**
     * Get user's earned badges
     */
    public function getEarnedBadges(User $user): Collection
    {
        return $user->earnedBadges()
            ->with('badge')
            ->orderBy('earned_at', 'desc')
            ->get()
            ->map(function ($userBadge) {
                return [
                    'id' => $userBadge->badge->id,
                    'code' => $userBadge->badge->code,
                    'name' => $userBadge->badge->name,
                    'description' => $userBadge->badge->description,
                    'icon' => $userBadge->badge->icon,
                    'category' => $userBadge->badge->category,
                    'type' => $userBadge->badge->type,
                    'earned_at' => $userBadge->earned_at,
                    'progress_value' => $userBadge->progress_value,
                ];
            });
    }

    /**
     * Calculate streak progress
     */
    private function calculateStreakProgress(User $user, Badge $badge): int
    {
        $code = $badge->code;

        if (str_contains($code, 'meal_streak')) {
            return $this->calculateCurrentStreak($user->id, 'meals');
        }

        if (str_contains($code, 'exercise_streak')) {
            return $this->calculateCurrentStreak($user->id, 'exercises');
        }

        if (str_contains($code, 'weight_streak')) {
            return $this->calculateCurrentStreak($user->id, 'weights');
        }

        return 0;
    }

    /**
     * Calculate meal progress
     */
    private function calculateMealProgress(User $user, Badge $badge): int
    {
        if ($badge->requirement_type === 'count') {
            return MealLog::forUser($user->id)->count();
        }

        if ($badge->requirement_type === 'days') {
            return MealLog::forUser($user->id)->distinct('date')->count('date');
        }

        return 0;
    }

    /**
     * Calculate exercise progress
     */
    private function calculateExerciseProgress(User $user, Badge $badge): int
    {
        if ($badge->requirement_type === 'count') {
            return ExerciseLog::forUser($user->id)->count();
        }

        if ($badge->requirement_type === 'calories') {
            return (int) ExerciseLog::forUser($user->id)->sum('calories_burned');
        }

        if ($badge->requirement_type === 'minutes') {
            return (int) ExerciseLog::forUser($user->id)->sum('duration_minutes');
        }

        return 0;
    }

    /**
     * Calculate weight progress
     */
    private function calculateWeightProgress(User $user, Badge $badge): int
    {
        if ($badge->requirement_type === 'count') {
            return WeightLog::forUser($user->id)->count();
        }

        if ($badge->requirement_type === 'kg') {
            $first = WeightLog::forUser($user->id)->orderBy('date', 'asc')->first();
            $last = WeightLog::forUser($user->id)->orderBy('date', 'desc')->first();

            if ($first && $last && str_contains($badge->code, 'loss')) {
                return (int) max(0, $first->weight - $last->weight);
            }
        }

        return 0;
    }

    /**
     * Calculate milestone progress
     */
    private function calculateMilestoneProgress(User $user, Badge $badge): int
    {
        if (str_contains($badge->code, 'total_logs')) {
            $meals = MealLog::forUser($user->id)->count();
            $exercises = ExerciseLog::forUser($user->id)->count();
            $weights = WeightLog::forUser($user->id)->count();

            return $meals + $exercises + $weights;
        }

        return 0;
    }

    /**
     * Calculate current streak for a type
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
}
