<?php

namespace App\Http\Controllers\Api\V1\Task;

use App\Domain\Task\Models\TaskNotification;
use App\Domain\Task\Services\TaskNotificationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskNotificationController extends Controller
{
    public function __construct(
        private TaskNotificationService $notificationService
    ) {}

    /**
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $this->notificationService->getUserNotifications($user);

        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => $this->notificationService->getUnreadCount($user),
            ]
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'data' => ['count' => $count]
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $notification = TaskNotification::where('id', $notificationId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $this->notificationService->markAsRead($notification);

        return response()->json([
            'message' => 'Notification marked as read',
            'data' => $notification->fresh()
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'message' => "Marked {$count} notifications as read",
            'data' => ['updated_count' => $count]
        ]);
    }

    /**
     * Get notification statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->notificationService->getNotificationStats($user);

        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, string $notificationId): JsonResponse
    {
        $notification = TaskNotification::where('id', $notificationId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted successfully'
        ]);
    }
}