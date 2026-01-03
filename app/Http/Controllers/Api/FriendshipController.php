<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FriendshipController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();

        // Get all accepted friendships
        $sentFriends = Friendship::where('user_id', $userId)
            ->where('status', 'accepted')
            ->with('friend:id,name,profile_photo_path')
            ->get()
            ->pluck('friend');

        $receivedFriends = Friendship::where('friend_id', $userId)
            ->where('status', 'accepted')
            ->with('user:id,name,profile_photo_path')
            ->get()
            ->pluck('user');

        $friends = $sentFriends->merge($receivedFriends)->unique('id')->values();

        return response()->success('Friends retrieved successfully', ['friends' => $friends]);
    }

    public function pendingRequests(): JsonResponse
    {
        $requests = Friendship::where('friend_id', auth()->id())
            ->where('status', 'pending')
            ->with('user:id,name,profile_photo_path')
            ->orderByDesc('created_at')
            ->get();

        return response()->success('Pending requests retrieved successfully', ['requests' => $requests]);
    }

    public function sentRequests(): JsonResponse
    {
        $requests = Friendship::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->with('friend:id,name,profile_photo_path')
            ->orderByDesc('created_at')
            ->get();

        return response()->success('Sent requests retrieved successfully', ['requests' => $requests]);
    }

    public function sendRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'friend_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $userId = auth()->id();
        $friendId = $request->friend_id;

        if ($userId === $friendId) {
            return response()->error('You cannot send a friend request to yourself', [], 400);
        }

        // Check if friendship already exists
        $existing = Friendship::where(function ($query) use ($userId, $friendId) {
            $query->where('user_id', $userId)->where('friend_id', $friendId);
        })->orWhere(function ($query) use ($userId, $friendId) {
            $query->where('user_id', $friendId)->where('friend_id', $userId);
        })->first();

        if ($existing) {
            if ($existing->status === 'accepted') {
                return response()->error('You are already friends', [], 400);
            }
            if ($existing->status === 'pending') {
                return response()->error('Friend request already pending', [], 400);
            }
            if ($existing->status === 'blocked') {
                return response()->error('Cannot send friend request', [], 400);
            }
        }

        $friendship = Friendship::create([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending',
        ]);

        return response()->created('Friend request sent successfully', ['friendship' => $friendship]);
    }

    public function acceptRequest(Friendship $friendship): JsonResponse
    {
        if ($friendship->friend_id !== auth()->id()) {
            return response()->forbidden('You are not authorized to accept this request');
        }

        if ($friendship->status !== 'pending') {
            return response()->error('This request is not pending', [], 400);
        }

        $friendship->accept();

        return response()->success('Friend request accepted', ['friendship' => $friendship->fresh()]);
    }

    public function rejectRequest(Friendship $friendship): JsonResponse
    {
        if ($friendship->friend_id !== auth()->id()) {
            return response()->forbidden('You are not authorized to reject this request');
        }

        if ($friendship->status !== 'pending') {
            return response()->error('This request is not pending', [], 400);
        }

        $friendship->reject();

        return response()->success('Friend request rejected');
    }

    public function cancelRequest(Friendship $friendship): JsonResponse
    {
        if ($friendship->user_id !== auth()->id()) {
            return response()->forbidden('You are not authorized to cancel this request');
        }

        if ($friendship->status !== 'pending') {
            return response()->error('This request is not pending', [], 400);
        }

        $friendship->delete();

        return response()->success('Friend request cancelled');
    }

    public function removeFriend(User $friend): JsonResponse
    {
        $userId = auth()->id();

        $friendship = Friendship::where(function ($query) use ($userId, $friend) {
            $query->where('user_id', $userId)->where('friend_id', $friend->id);
        })->orWhere(function ($query) use ($userId, $friend) {
            $query->where('user_id', $friend->id)->where('friend_id', $userId);
        })->where('status', 'accepted')->first();

        if (!$friendship) {
            return response()->error('You are not friends with this user', [], 400);
        }

        $friendship->delete();

        return response()->success('Friend removed successfully');
    }

    public function blockUser(User $user): JsonResponse
    {
        $userId = auth()->id();

        if ($userId === $user->id) {
            return response()->error('You cannot block yourself', [], 400);
        }

        // Find or create friendship record
        $friendship = Friendship::where(function ($query) use ($userId, $user) {
            $query->where('user_id', $userId)->where('friend_id', $user->id);
        })->orWhere(function ($query) use ($userId, $user) {
            $query->where('user_id', $user->id)->where('friend_id', $userId);
        })->first();

        if ($friendship) {
            $friendship->update([
                'user_id' => $userId,
                'friend_id' => $user->id,
                'status' => 'blocked',
            ]);
        } else {
            Friendship::create([
                'user_id' => $userId,
                'friend_id' => $user->id,
                'status' => 'blocked',
            ]);
        }

        return response()->success('User blocked successfully');
    }

    public function unblockUser(User $user): JsonResponse
    {
        $deleted = Friendship::where('user_id', auth()->id())
            ->where('friend_id', $user->id)
            ->where('status', 'blocked')
            ->delete();

        if (!$deleted) {
            return response()->error('User is not blocked', [], 400);
        }

        return response()->success('User unblocked successfully');
    }

    public function friendProgress(User $friend): JsonResponse
    {
        // Check if actually friends
        if (!auth()->user()->isFriendsWith($friend)) {
            return response()->forbidden('You are not friends with this user');
        }

        // Get friend's recent activity (last 7 days)
        $startDate = now()->subDays(7)->toDateString();

        $mealLogs = \App\Models\MealLog::where('user_id', $friend->id)
            ->whereDate('logged_at', '>=', $startDate)
            ->selectRaw('DATE(logged_at) as date, COUNT(*) as count, SUM(calories) as calories')
            ->groupBy('date')
            ->get();

        $exerciseLogs = \App\Models\ExerciseLog::where('user_id', $friend->id)
            ->whereDate('logged_at', '>=', $startDate)
            ->selectRaw('DATE(logged_at) as date, COUNT(*) as count, SUM(calories_burned) as calories')
            ->groupBy('date')
            ->get();

        // Get latest weight
        $latestWeight = \App\Models\WeightLog::where('user_id', $friend->id)
            ->orderByDesc('logged_at')
            ->first();

        return response()->success('Friend progress retrieved successfully', [
            'friend' => [
                'id' => $friend->id,
                'name' => $friend->name,
                'profile_photo_path' => $friend->profile_photo_path,
            ],
            'meal_summary' => $mealLogs,
            'exercise_summary' => $exerciseLogs,
            'latest_weight' => $latestWeight?->weight,
        ]);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $users = User::where('id', '!=', auth()->id())
            ->where(function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->q}%")
                      ->orWhere('email', 'like', "%{$request->q}%");
            })
            ->select('id', 'name', 'profile_photo_path')
            ->limit(20)
            ->get();

        return response()->success('Users found', ['users' => $users]);
    }
}
