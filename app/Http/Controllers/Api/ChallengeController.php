<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Services\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function __construct(
        private BadgeService $badgeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Challenge::active()
            ->withCount('participants');

        // Filter by status
        $status = $request->get('status', 'ongoing');
        switch ($status) {
            case 'ongoing':
                $query->ongoing();
                break;
            case 'upcoming':
                $query->upcoming();
                break;
            case 'all':
                // No filter
                break;
        }

        // Filter by goal type
        if ($request->has('goal_type')) {
            $query->where('goal_type', $request->goal_type);
        }

        $challenges = $query->orderBy('start_date')->paginate($request->get('per_page', 15));

        // Add participation status for authenticated user
        if (auth()->check()) {
            $userId = auth()->id();
            $participatingIds = ChallengeParticipant::where('user_id', $userId)
                ->pluck('challenge_id')
                ->toArray();

            $challenges->getCollection()->transform(function ($challenge) use ($participatingIds) {
                $challenge->is_participating = in_array($challenge->id, $participatingIds);
                return $challenge;
            });
        }

        return response()->success($challenges, 'Challenges retrieved successfully');
    }

    public function show(Challenge $challenge): JsonResponse
    {
        $challenge->loadCount('participants');
        $challenge->load('badge');

        $data = ['challenge' => $challenge];

        if (auth()->check()) {
            $participation = ChallengeParticipant::where('challenge_id', $challenge->id)
                ->where('user_id', auth()->id())
                ->first();

            $data['participation'] = $participation;
        }

        // Get top participants
        $data['leaderboard'] = $challenge->participants()
            ->with('user:id,name,profile_photo_path')
            ->orderByDesc('progress')
            ->limit(10)
            ->get();

        return response()->success($data, 'Challenge retrieved successfully');
    }

    public function join(Challenge $challenge): JsonResponse
    {
        $userId = auth()->id();

        // Check if already participating
        if ($challenge->isJoinedBy(auth()->user())) {
            return response()->error('You are already participating in this challenge', [], 400);
        }

        // Check if challenge is still open
        if (!$challenge->is_active) {
            return response()->error('This challenge is not active', [], 400);
        }

        if ($challenge->hasEnded()) {
            return response()->error('This challenge has ended', [], 400);
        }

        // Check max participants
        if ($challenge->max_participants && $challenge->participants_count >= $challenge->max_participants) {
            return response()->error('This challenge is full', [], 400);
        }

        $participant = ChallengeParticipant::create([
            'challenge_id' => $challenge->id,
            'user_id' => $userId,
            'joined_at' => now(),
        ]);

        return response()->created([
            'participation' => $participant,
        ], 'Successfully joined the challenge');
    }

    public function leave(Challenge $challenge): JsonResponse
    {
        $participant = ChallengeParticipant::where('challenge_id', $challenge->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$participant) {
            return response()->error('You are not participating in this challenge', [], 400);
        }

        if ($participant->status === 'completed') {
            return response()->error('Cannot leave a completed challenge', [], 400);
        }

        $participant->update(['status' => 'withdrawn']);

        return response()->success(null, 'Successfully left the challenge');
    }

    public function myChallenges(Request $request): JsonResponse
    {
        $query = ChallengeParticipant::where('user_id', auth()->id())
            ->with(['challenge' => function ($q) {
                $q->withCount('participants');
            }]);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $participations = $query->orderByDesc('joined_at')
            ->paginate($request->get('per_page', 15));

        return response()->success($participations, 'My challenges retrieved successfully');
    }

    public function updateProgress(Challenge $challenge): JsonResponse
    {
        $participant = ChallengeParticipant::where('challenge_id', $challenge->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$participant) {
            return response()->error('You are not participating in this challenge', [], 400);
        }

        if ($participant->status !== 'active') {
            return response()->error('Cannot update progress for non-active participation', [], 400);
        }

        // Calculate progress based on goal type
        $currentValue = $this->calculateCurrentValue($challenge, auth()->id());
        $participant->updateProgress($currentValue);

        // Award badge if completed and challenge has a badge
        if ($participant->isCompleted() && $challenge->badge_id) {
            $this->badgeService->awardBadge(auth()->user(), $challenge->badge);
        }

        return response()->success([
            'participation' => $participant->fresh(),
        ], 'Progress updated successfully');
    }

    private function calculateCurrentValue(Challenge $challenge, int $userId): float
    {
        $startDate = $challenge->start_date;
        $endDate = min($challenge->end_date, now()->toDateString());

        switch ($challenge->goal_type) {
            case 'weight_loss':
                // Get weight difference from start to now
                $startWeight = \App\Models\WeightLog::where('user_id', $userId)
                    ->whereDate('logged_at', '>=', $startDate)
                    ->orderBy('logged_at')
                    ->value('weight');

                $currentWeight = \App\Models\WeightLog::where('user_id', $userId)
                    ->whereDate('logged_at', '<=', $endDate)
                    ->orderByDesc('logged_at')
                    ->value('weight');

                if ($startWeight && $currentWeight) {
                    return max(0, $startWeight - $currentWeight);
                }
                return 0;

            case 'exercise_count':
                return \App\Models\ExerciseLog::where('user_id', $userId)
                    ->whereDate('logged_at', '>=', $startDate)
                    ->whereDate('logged_at', '<=', $endDate)
                    ->count();

            case 'streak':
                // Count consecutive days with any log
                $days = 0;
                $checkDate = now();
                while ($checkDate >= $startDate) {
                    $hasLog = \App\Models\MealLog::where('user_id', $userId)
                        ->whereDate('logged_at', $checkDate)
                        ->exists()
                        || \App\Models\ExerciseLog::where('user_id', $userId)
                        ->whereDate('logged_at', $checkDate)
                        ->exists();

                    if ($hasLog) {
                        $days++;
                        $checkDate = $checkDate->subDay();
                    } else {
                        break;
                    }
                }
                return $days;

            case 'meal_log':
                return \App\Models\MealLog::where('user_id', $userId)
                    ->whereDate('logged_at', '>=', $startDate)
                    ->whereDate('logged_at', '<=', $endDate)
                    ->count();

            default:
                return 0;
        }
    }

    public function leaderboard(Challenge $challenge, Request $request): JsonResponse
    {
        $leaderboard = $challenge->participants()
            ->with('user:id,name,profile_photo_path')
            ->orderByDesc('progress')
            ->paginate($request->get('per_page', 20));

        return response()->success($leaderboard, 'Leaderboard retrieved successfully');
    }
}
