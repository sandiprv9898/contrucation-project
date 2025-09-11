<?php

namespace App\Domain\Task\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TaskNotification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'task_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Notification types
    public const TYPE_ASSIGNMENT = 'assignment';
    public const TYPE_DUE_DATE = 'due_date';
    public const TYPE_STATUS_CHANGE = 'status_change';
    public const TYPE_COMMENT = 'comment';
    public const TYPE_TIME_LOG = 'time_log';
    public const TYPE_ATTACHMENT = 'attachment';

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task that this notification is about
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Get human-readable time difference
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get notification icon based on type
     */
    public function getIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_ASSIGNMENT => 'user-plus',
            self::TYPE_DUE_DATE => 'clock',
            self::TYPE_STATUS_CHANGE => 'refresh-cw',
            self::TYPE_COMMENT => 'message-circle',
            self::TYPE_TIME_LOG => 'timer',
            self::TYPE_ATTACHMENT => 'paperclip',
            default => 'bell',
        };
    }

    /**
     * Get notification color based on type
     */
    public function getColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_ASSIGNMENT => 'blue',
            self::TYPE_DUE_DATE => 'orange',
            self::TYPE_STATUS_CHANGE => 'green',
            self::TYPE_COMMENT => 'purple',
            self::TYPE_TIME_LOG => 'indigo',
            self::TYPE_ATTACHMENT => 'gray',
            default => 'gray',
        };
    }

    /**
     * Scope to get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope to get notifications for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get notifications of a specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get recent notifications (last 30 days)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays(30));
    }
}