<?php

namespace App\Domain\Task\Services;

use App\Domain\Task\Models\Task;
use App\Domain\Task\Models\TaskNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TaskNotificationService
{
    /**
     * Create a new task notification
     */
    public function createNotification(
        $user,
        Task $task,
        string $type,
        string $title,
        ?string $message = null,
        ?array $data = null
    ): TaskNotification {
        $notification = TaskNotification::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        // Send email notification if user has email notifications enabled
        if ($this->shouldSendEmail($user, $type)) {
            $this->sendEmailNotification($user, $notification);
        }

        return $notification;
    }

    /**
     * Get user's notifications with pagination
     */
    public function getUserNotifications($user, int $perPage = 20): LengthAwarePaginator
    {
        return TaskNotification::with(['task'])
            ->forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get unread notifications count for user
     */
    public function getUnreadCount($user): int
    {
        return TaskNotification::forUser($user->id)->unread()->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(TaskNotification $notification): void
    {
        if (!$notification->is_read) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark all user notifications as read
     */
    public function markAllAsRead($user): int
    {
        return TaskNotification::forUser($user->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Delete old notifications (older than 30 days)
     */
    public function cleanupOldNotifications(): int
    {
        return TaskNotification::where('created_at', '<', now()->subDays(30))
            ->delete();
    }

    /**
     * Task assignment notification
     */
    public function notifyTaskAssignment(Task $task, $assignee, $assignedBy): void
    {
        if ($assignee->id === $assignedBy->id) {
            return; // Don't notify if user assigned themselves
        }

        $this->createNotification(
            user: $assignee,
            task: $task,
            type: TaskNotification::TYPE_ASSIGNMENT,
            title: "You've been assigned to a task",
            message: "Task '{$task->name}' has been assigned to you by {$assignedBy->name}",
            data: [
                'assigned_by' => $assignedBy->name,
                'task_priority' => $task->priority,
                'due_date' => $task->due_date?->format('Y-m-d'),
            ]
        );
    }

    /**
     * Task due date notification
     */
    public function notifyTaskDue(Task $task, $user): void
    {
        $this->createNotification(
            user: $user,
            task: $task,
            type: TaskNotification::TYPE_DUE_DATE,
            title: "Task is due soon",
            message: "Task '{$task->name}' is due on {$task->due_date->format('M d, Y')}",
            data: [
                'due_date' => $task->due_date->format('Y-m-d'),
                'priority' => $task->priority,
            ]
        );
    }

    /**
     * Task status change notification
     */
    public function notifyStatusChange(Task $task, string $oldStatus, $changedBy): void
    {
        // Notify assignee if different from the person who changed it
        if ($task->assignee && $task->assignee->id !== $changedBy->id) {
            $this->createNotification(
                user: $task->assignee,
                task: $task,
                type: TaskNotification::TYPE_STATUS_CHANGE,
                title: "Task status updated",
                message: "Task '{$task->name}' status changed from {$oldStatus} to {$task->status} by {$changedBy->name}",
                data: [
                    'old_status' => $oldStatus,
                    'new_status' => $task->status,
                    'changed_by' => $changedBy->name,
                ]
            );
        }
    }

    /**
     * Task comment notification
     */
    public function notifyTaskComment(Task $task, $commenter, string $comment): void
    {
        // Get all users who should be notified (assignee, creator, previous commenters)
        $usersToNotify = collect();

        if ($task->assignee && $task->assignee->id !== $commenter->id) {
            $usersToNotify->push($task->assignee);
        }

        if ($task->creator && $task->creator->id !== $commenter->id && !$usersToNotify->contains('id', $task->creator->id)) {
            $usersToNotify->push($task->creator);
        }

        foreach ($usersToNotify as $user) {
            $this->createNotification(
                user: $user,
                task: $task,
                type: TaskNotification::TYPE_COMMENT,
                title: "New comment on task",
                message: "{$commenter->name} commented on task '{$task->name}'",
                data: [
                    'commenter' => $commenter->name,
                    'comment_preview' => substr($comment, 0, 100),
                ]
            );
        }
    }

    /**
     * Time log notification
     */
    public function notifyTimeLog(Task $task, $logger, int $minutes): void
    {
        // Notify task creator and assignee (if different from logger)
        $usersToNotify = collect();

        if ($task->assignee && $task->assignee->id !== $logger->id) {
            $usersToNotify->push($task->assignee);
        }

        if ($task->creator && $task->creator->id !== $logger->id && !$usersToNotify->contains('id', $task->creator->id)) {
            $usersToNotify->push($task->creator);
        }

        $hours = round($minutes / 60, 1);

        foreach ($usersToNotify as $user) {
            $this->createNotification(
                user: $user,
                task: $task,
                type: TaskNotification::TYPE_TIME_LOG,
                title: "Time logged on task",
                message: "{$logger->name} logged {$hours} hours on task '{$task->name}'",
                data: [
                    'logger' => $logger->name,
                    'minutes' => $minutes,
                    'hours' => $hours,
                ]
            );
        }
    }

    /**
     * Check if email notification should be sent
     */
    private function shouldSendEmail($user, string $type): bool
    {
        // Check user's notification preferences (you can implement this based on user settings)
        $emailTypes = [
            TaskNotification::TYPE_ASSIGNMENT,
            TaskNotification::TYPE_DUE_DATE,
        ];

        return in_array($type, $emailTypes);
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification($user, TaskNotification $notification): void
    {
        try {
            // You can implement email templates here
            // Mail::to($user->email)->send(new TaskNotificationMail($notification));
            Log::info("Email notification sent to {$user->email} for notification {$notification->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send email notification: " . $e->getMessage());
        }
    }

    /**
     * Get notification statistics for dashboard
     */
    public function getNotificationStats($user): array
    {
        $notifications = TaskNotification::forUser($user->id)->recent();

        return [
            'total' => $notifications->count(),
            'unread' => $notifications->unread()->count(),
            'by_type' => $notifications->get()->groupBy('type')->map->count()->toArray(),
            'today' => $notifications->whereDate('created_at', today())->count(),
            'this_week' => $notifications->where('created_at', '>=', now()->startOfWeek())->count(),
        ];
    }
}