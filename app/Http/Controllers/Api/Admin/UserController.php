<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Get paginated list of users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereNull('disabled_at');
            } elseif ($request->status === 'disabled') {
                $query->whereNotNull('disabled_at');
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate($request->get('per_page', 20));

        return response()->success($users, 'Users retrieved successfully');
    }

    /**
     * Get user details
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['surveyResponses', 'calculations']);

        $stats = [
            'meal_logs_count' => $user->mealLogs()->count(),
            'exercise_logs_count' => $user->exerciseLogs()->count(),
            'weight_logs_count' => $user->weightLogs()->count(),
            'posts_count' => $user->posts()->count(),
            'comments_count' => $user->comments()->count(),
            'badges_count' => $user->earnedBadges()->count(),
        ];

        return response()->success([
            'user' => $user,
            'stats' => $stats,
        ], 'User details retrieved');
    }

    /**
     * Update user status (enable/disable)
     */
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:enable,disable',
            'reason' => 'required_if:action,disable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        if ($request->action === 'disable') {
            $user->update([
                'disabled_at' => now(),
                'disabled_reason' => $request->reason,
            ]);
            // Revoke all tokens
            $user->tokens()->delete();
        } else {
            $user->update([
                'disabled_at' => null,
                'disabled_reason' => null,
            ]);
        }

        return response()->success(['user' => $user->fresh()], "User {$request->action}d successfully");
    }

    /**
     * Get user activity log
     */
    public function activity(Request $request, User $user): JsonResponse
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $activity = [
            'meal_logs' => $user->mealLogs()
                ->where('created_at', '>=', $startDate)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(),
            'exercise_logs' => $user->exerciseLogs()
                ->where('created_at', '>=', $startDate)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(),
            'weight_logs' => $user->weightLogs()
                ->where('created_at', '>=', $startDate)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(),
        ];

        return response()->success($activity, 'User activity retrieved');
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $user->assignRole($request->role);

        return response()->success([
            'user' => $user->fresh(),
            'roles' => $user->getRoleNames(),
        ], 'Role assigned successfully');
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request, User $user, string $role): JsonResponse
    {
        if (!\Spatie\Permission\Models\Role::where('name', $role)->exists()) {
            return response()->error('Role not found', 404);
        }

        $user->removeRole($role);

        return response()->success([
            'user' => $user->fresh(),
            'roles' => $user->getRoleNames(),
        ], 'Role removed successfully');
    }
}
