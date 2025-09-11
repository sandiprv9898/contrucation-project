<?php

namespace App\Domain\Task\Models;

use App\Domain\Task\Enums\TaskDependencyType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDependency extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'task_id',
        'depends_on_task_id',
        'dependency_type',
        'lag_days',
    ];

    protected $casts = [
        'lag_days' => 'integer',
        'dependency_type' => TaskDependencyType::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function prerequisiteTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'depends_on_task_id');
    }

    // Scopes
    public function scopeByTask($query, string $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    public function scopeByPrerequisite($query, string $prerequisiteTaskId)
    {
        return $query->where('depends_on_task_id', $prerequisiteTaskId);
    }

    public function scopeByType($query, TaskDependencyType $type)
    {
        return $query->where('dependency_type', $type);
    }

    // Business Logic Methods
    public function isBlocking(): bool
    {
        $prerequisite = $this->prerequisiteTask;
        
        if (!$prerequisite) {
            return false;
        }

        return match($this->dependency_type) {
            TaskDependencyType::FINISH_TO_START => !$prerequisite->status->isCompleted(),
            TaskDependencyType::START_TO_START => !$prerequisite->status->isActive() && !$prerequisite->status->isCompleted(),
            TaskDependencyType::FINISH_TO_FINISH => !$prerequisite->status->isCompleted(),
            TaskDependencyType::START_TO_FINISH => !$prerequisite->status->isActive() && !$prerequisite->status->isCompleted(),
        };
    }

    public function getEarliestStartDate(): ?\Carbon\Carbon
    {
        $prerequisite = $this->prerequisiteTask;
        
        if (!$prerequisite) {
            return null;
        }

        $baseDate = match($this->dependency_type) {
            TaskDependencyType::FINISH_TO_START => $prerequisite->completed_at ?? $prerequisite->due_date,
            TaskDependencyType::START_TO_START => $prerequisite->start_date,
            TaskDependencyType::FINISH_TO_FINISH => $prerequisite->completed_at ?? $prerequisite->due_date,
            TaskDependencyType::START_TO_FINISH => $prerequisite->start_date,
        };

        if (!$baseDate) {
            return null;
        }

        return $baseDate->addDays($this->lag_days);
    }

    public function validateDependency(): array
    {
        $errors = [];
        
        // Check for circular dependencies
        if ($this->hasCircularDependency()) {
            $errors[] = 'Circular dependency detected';
        }
        
        // Check if tasks belong to the same project
        if (!$this->belongToSameProject()) {
            $errors[] = 'Tasks must belong to the same project';
        }
        
        // Check if dependency makes logical sense
        if (!$this->isLogicalDependency()) {
            $errors[] = 'Dependency relationship is not logical';
        }
        
        return $errors;
    }

    protected function hasCircularDependency(): bool
    {
        $visited = collect();
        $stack = collect([$this->task_id]);
        
        while ($stack->isNotEmpty()) {
            $currentTaskId = $stack->pop();
            
            if ($visited->contains($currentTaskId)) {
                return true; // Circular dependency found
            }
            
            $visited->push($currentTaskId);
            
            // Add all tasks that depend on this task to the stack
            $dependentTasks = TaskDependency::where('depends_on_task_id', $currentTaskId)
                ->pluck('task_id');
            
            $stack = $stack->merge($dependentTasks);
        }
        
        return false;
    }

    protected function belongToSameProject(): bool
    {
        $task = $this->task;
        $prerequisiteTask = $this->prerequisiteTask;
        
        return $task && $prerequisiteTask && $task->project_id === $prerequisiteTask->project_id;
    }

    protected function isLogicalDependency(): bool
    {
        $task = $this->task;
        $prerequisiteTask = $this->prerequisiteTask;
        
        if (!$task || !$prerequisiteTask) {
            return false;
        }
        
        // A task cannot depend on itself
        if ($task->id === $prerequisiteTask->id) {
            return false;
        }
        
        // A parent task cannot depend on its child task
        if ($task->id === $prerequisiteTask->parent_task_id) {
            return false;
        }
        
        // A child task cannot depend on its parent task (this creates logical issues)
        if ($prerequisiteTask->id === $task->parent_task_id) {
            return false;
        }
        
        return true;
    }
}