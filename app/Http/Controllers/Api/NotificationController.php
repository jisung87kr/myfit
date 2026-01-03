<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = $request->get('per_page', 20);

        $query = $user->notifications();

        // Filter by read/unread
        if ($request->has('unread_only') && $request->unread_only) {
            $query = $user->unreadNotifications();
        }

        $notifications = $query->paginate($perPage);

        return response()->success([
            'notifications' => $notifications->items(),
            'unread_count' => $user->unreadNotifications()->count(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ], 'Notifications retrieved successfully');
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(): JsonResponse
    {
        $count = auth()->user()->unreadNotifications()->count();

        return response()->success([
            'unread_count' => $count,
        ], 'Unread count retrieved');
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->notFound('Notification not found');
        }

        $notification->markAsRead();

        return response()->success([
            'notification' => $notification,
        ], 'Notification marked as read');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->success(null, 'All notifications marked as read');
    }

    /**
     * Delete a notification
     */
    public function destroy(string $id): JsonResponse
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->notFound('Notification not found');
        }

        $notification->delete();

        return response()->success(null, 'Notification deleted');
    }

    /**
     * Delete all read notifications
     */
    public function deleteRead(): JsonResponse
    {
        $deleted = auth()->user()
            ->notifications()
            ->whereNotNull('read_at')
            ->delete();

        return response()->success([
            'deleted_count' => $deleted,
        ], 'Read notifications deleted');
    }
}
