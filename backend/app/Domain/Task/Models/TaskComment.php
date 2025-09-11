<?php

namespace App\Domain\Task\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskComment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'task_id',
        'user_id',
        'comment',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByTask($query, string $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Business Logic Methods
    public function canBeEditedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function canBeDeletedBy(User $user): bool
    {
        return $this->user_id === $user->id || $user->hasRole(['admin', 'project_manager']);
    }

    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function isRecent(int $hours = 24): bool
    {
        return $this->created_at->gte(now()->subHours($hours));
    }
}