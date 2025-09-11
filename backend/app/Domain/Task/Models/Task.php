<?php

namespace App\Domain\Task\Models;

use App\Domain\Project\Models\Project;
use App\Domain\Project\Models\ProjectPhase;
use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskStatus;
use App\Domain\Task\Enums\TaskType;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'project_id',
        'phase_id',
        'parent_task_id',
        'name',
        'description',
        'status',
        'priority',
        'task_type',
        'assigned_to_id',
        'created_by_id',
        'estimated_hours',
        'actual_hours',
        'progress_percentage',
        'start_date',
        'due_date',
        'completed_at',
        'task_order',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'progress_percentage' => 'integer',
        'task_order' => 'integer',
        'metadata' => 'array',
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
        'task_type' => TaskType::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id');
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id')->orderBy('task_order');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    public function dependentTasks(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at', 'desc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', TaskStatus::IN_PROGRESS);
    }

    public function scopeByStatus($query, TaskStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, TaskPriority $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByType($query, TaskType $type)
    {
        return $query->where('task_type', $type);
    }

    public function scopeByProject($query, string $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByAssignee($query, string $userId)
    {
        return $query->where('assigned_to_id', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', [TaskStatus::COMPLETED, TaskStatus::CANCELLED]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', TaskStatus::COMPLETED);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_task_id');
    }

    public function scopeSubTasks($query)
    {
        return $query->whereNotNull('parent_task_id');
    }

    public function scopeByPhase($query, string $phaseId)
    {
        return $query->where('phase_id', $phaseId);
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->where('due_date', '<=', now()->addDays($days))
                    ->where('due_date', '>=', now())
                    ->whereNotIn('status', [TaskStatus::COMPLETED, TaskStatus::CANCELLED]);
    }

    // Business Logic Methods
    public function calculateProgress(): int
    {
        if ($this->subTasks()->count() === 0) {
            return $this->progress_percentage;
        }

        $totalSubTasks = $this->subTasks()->count();
        if ($totalSubTasks === 0) {
            return $this->progress_percentage;
        }

        $totalProgress = $this->subTasks()->sum('progress_percentage');
        return round($totalProgress / $totalSubTasks);
    }

    public function updateCalculatedProgress(): void
    {
        $calculatedProgress = $this->calculateProgress();
        $this->update(['progress_percentage' => $calculatedProgress]);

        // Update parent task progress if this is a subtask
        if ($this->parent_task_id && $this->parentTask) {
            $this->parentTask->updateCalculatedProgress();
        }
    }

    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               !in_array($this->status, [TaskStatus::COMPLETED, TaskStatus::CANCELLED]);
    }

    public function isDueSoon(int $days = 7): bool
    {
        return $this->due_date && 
               $this->due_date->isFuture() && 
               $this->due_date->diffInDays(now()) <= $days &&
               !in_array($this->status, [TaskStatus::COMPLETED, TaskStatus::CANCELLED]);
    }

    public function getTimeVariance(): float
    {
        if (!$this->estimated_hours) {
            return 0;
        }
        
        return $this->actual_hours - $this->estimated_hours;
    }

    public function getTimeVariancePercentage(): float
    {
        if (!$this->estimated_hours) {
            return 0;
        }
        
        return (($this->actual_hours - $this->estimated_hours) / $this->estimated_hours) * 100;
    }

    public function getDurationInDays(): ?int
    {
        if (!$this->start_date || !$this->due_date) {
            return null;
        }
        
        return $this->start_date->diffInDays($this->due_date);
    }

    public function canBeDeleted(): bool
    {
        // Cannot delete if task has dependencies or subtasks
        return $this->dependentTasks()->count() === 0 && 
               $this->subTasks()->count() === 0 &&
               !in_array($this->status, [TaskStatus::IN_PROGRESS]);
    }

    public function canBeEdited(): bool
    {
        return !in_array($this->status, [TaskStatus::COMPLETED, TaskStatus::CANCELLED]);
    }

    public function canBeStarted(): bool
    {
        if ($this->status !== TaskStatus::NOT_STARTED) {
            return false;
        }

        // Check if all dependencies are completed
        foreach ($this->dependencies as $dependency) {
            $prerequisiteTask = $dependency->prerequisiteTask;
            if (!$prerequisiteTask || $prerequisiteTask->status !== TaskStatus::COMPLETED) {
                return false;
            }
        }

        return true;
    }

    public function hasBlockingDependencies(): bool
    {
        return !$this->canBeStarted() && $this->status === TaskStatus::NOT_STARTED;
    }

    // Status transition methods
    public function start(): bool
    {
        if ($this->canBeStarted()) {
            return $this->update([
                'status' => TaskStatus::IN_PROGRESS,
                'start_date' => $this->start_date ?: now()->toDateString()
            ]);
        }
        
        return false;
    }

    public function putInReview(): bool
    {
        if ($this->status === TaskStatus::IN_PROGRESS) {
            return $this->update(['status' => TaskStatus::REVIEW]);
        }
        
        return false;
    }

    public function complete(): bool
    {
        if (in_array($this->status, [TaskStatus::IN_PROGRESS, TaskStatus::REVIEW])) {
            $updated = $this->update([
                'status' => TaskStatus::COMPLETED,
                'progress_percentage' => 100,
                'completed_at' => now()
            ]);

            if ($updated) {
                // Update project progress
                $this->project->updateCalculatedProgress();
            }

            return $updated;
        }
        
        return false;
    }

    public function putOnHold(): bool
    {
        if (in_array($this->status, [TaskStatus::NOT_STARTED, TaskStatus::IN_PROGRESS, TaskStatus::REVIEW])) {
            return $this->update(['status' => TaskStatus::ON_HOLD]);
        }
        
        return false;
    }

    public function cancel(): bool
    {
        if (!in_array($this->status, [TaskStatus::COMPLETED])) {
            $updated = $this->update(['status' => TaskStatus::CANCELLED]);

            if ($updated) {
                // Update project progress
                $this->project->updateCalculatedProgress();
            }

            return $updated;
        }
        
        return false;
    }

    public function reopen(): bool
    {
        if ($this->status === TaskStatus::COMPLETED) {
            return $this->update([
                'status' => TaskStatus::IN_PROGRESS,
                'completed_at' => null
            ]);
        }
        
        return false;
    }

    // Hierarchy methods
    public function isTopLevel(): bool
    {
        return is_null($this->parent_task_id);
    }

    public function isSubTask(): bool
    {
        return !is_null($this->parent_task_id);
    }

    public function getLevel(): int
    {
        $level = 0;
        $task = $this;
        
        while ($task->parent_task_id) {
            $level++;
            $task = $task->parentTask;
        }
        
        return $level;
    }

    public function getAllSubTasks(): \Illuminate\Database\Eloquent\Collection
    {
        $allSubTasks = collect();
        
        foreach ($this->subTasks as $subTask) {
            $allSubTasks->push($subTask);
            $allSubTasks = $allSubTasks->merge($subTask->getAllSubTasks());
        }
        
        return $allSubTasks;
    }
}