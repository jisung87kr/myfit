<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Services\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function __construct(
        private BadgeService $badgeService
    ) {}

    /**
     * Get all badges with user progress
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $badges = $this->badgeService->getBadgesWithProgress($user);

        $grouped = $badges->groupBy('category');

        return response()->success('Badges retrieved successfully', [
            'badges' => $badges,
            'by_category' => $grouped,
            'summary' => [
                'total' => $badges->count(),
                'earned' => $badges->where('is_earned', true)->count(),
                'in_progress' => $badges->where('is_earned', false)->where('progress', '>', 0)->count(),
            ],
        ]);
    }

    /**
     * Get user's earned badges
     */
    public function earned(): JsonResponse
    {
        $user = auth()->user();
        $badges = $this->badgeService->getEarnedBadges($user);

        return response()->success('Earned badges retrieved successfully', [
            'badges' => $badges,
            'count' => $badges->count(),
        ]);
    }

    /**
     * Check for new badges
     */
    public function check(): JsonResponse
    {
        $user = auth()->user();
        $newBadges = $this->badgeService->checkAndAwardBadges($user);

        if ($newBadges->isEmpty()) {
            return response()->success('No new badges earned', [
                'new_badges' => [],
            ]);
        }

        return response()->success('New badges earned!', [
            'new_badges' => $newBadges->map(fn($b) => [
                'id' => $b->id,
                'code' => $b->code,
                'name' => $b->name,
                'description' => $b->description,
                'icon' => $b->icon,
                'category' => $b->category,
                'type' => $b->type,
            ]),
        ]);
    }

    /**
     * Get badge details
     */
    public function show(Badge $badge): JsonResponse
    {
        $user = auth()->user();
        $isEarned = $this->badgeService->userHasBadge($user, $badge);
        $progress = $isEarned ? $badge->requirement_value : $this->badgeService->calculateProgress($user, $badge);

        return response()->success('Badge details retrieved successfully', [
            'badge' => [
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
            ],
        ]);
    }

    /**
     * Get available badge categories
     */
    public function categories(): JsonResponse
    {
        $categories = Badge::active()
            ->select('category')
            ->distinct()
            ->pluck('category');

        return response()->success('Categories retrieved successfully', [
            'categories' => $categories,
        ]);
    }
}
