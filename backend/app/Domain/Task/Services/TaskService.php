<?php

namespace App\Domain\Task\Services;

use App\Domain\Task\DTOs\CreateTaskDTO;
use App\Domain\Task\DTOs\UpdateTaskDTO;
use App\Domain\Task\Enums\TaskStatus;
use App\Domain\Task\Models\Task;
use App\Domain\Task\Models\TaskDependency;
use App\Domain\Task\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function createTask(CreateTaskDTO $data): Task
    {
        return DB::transaction(function () use ($data) {
            $task = $this->taskRepository->create($data->toArray());

            // If this is a subtask, update parent task ordering
            if ($task->parent_task_id) {
                $this->reorderSiblingTasks($task->parent_task_id);
            }

            return $task->load([
                'project',
                'phase',
                'parentTask',
                'assignedTo',
                'createdBy'
            ]);
        });
    }

    public function updateTask(string $id, UpdateTaskDTO $data): Task
    {
        return DB::transaction(function () use ($id, $data) {
            $task = $this->taskRepository->update($id, $data->toArray());

            // Update calculated progress if status changed to completed
            if ($data->status === TaskStatus::COMPLETED) {
                $task->update(['progress_percentage' => 100]);
                $task->project->updateCalculatedProgress();
            }

            // Update parent progress if this is a subtask
            if ($task->parent_task_id && $task->parentTask) {
                $task->parentTask->updateCalculatedProgress();
            }

            return $task->load([
                'project',
                'phase', 
                'parentTask',
                'assignedTo',
                'createdBy'
            ]);
        });
    }

    public function deleteTask(string $id): bool
    {
        $task = $this->taskRepository->findById($id);

        if (!$task) {
            throw new ModelNotFoundException("Task with ID {$id} not found");
        }

        if (!$task->canBeDeleted()) {
            throw new \InvalidArgumentException("Cannot delete task with dependencies or active subtasks");
        }

        return DB::transaction(function () use ($task) {
            // Delete all dependencies involving this task
            TaskDependency::where('task_id', $task->id)
                ->orWhere('depends_on_task_id', $task->id)
                ->delete();

            // Delete the task
            $deleted = $this->taskRepository->delete($task->id);

            // Update project progress
            if ($deleted) {
                $task->project->updateCalculatedProgress();
            }

            return $deleted;
        });
    }

    public function getTask(string $id): Task
    {
        $task = $this->taskRepository->findById($id, [
            'project',
            'phase',
            'parentTask',
            'subTasks.assignedTo',
            'assignedTo',
            'createdBy',
            'dependencies.prerequisiteTask',
            'dependentTasks.task',
            'comments.user'
        ]);

        if (!$task) {
            throw new ModelNotFoundException("Task with ID {$id} not found");
        }

        return $task;
    }

    public function updateTaskStatus(string $id, TaskStatus $status): Task
    {
        $task = $this->taskRepository->findById($id);

        if (!$task) {
            throw new ModelNotFoundException("Task with ID {$id} not found");
        }

        return DB::transaction(function () use ($task, $status) {
            $updated = match($status) {
                TaskStatus::IN_PROGRESS => $task->start(),
                TaskStatus::REVIEW => $task->putInReview(),
                TaskStatus::COMPLETED => $task->complete(),
                TaskStatus::ON_HOLD => $task->putOnHold(),
                TaskStatus::CANCELLED => $task->cancel(),
                default => $task->update(['status' => $status])
            };

            if (!$updated) {
                throw new \InvalidArgumentException("Cannot transition from {$task->status->value} to {$status->value}");
            }

            // Trigger status-specific business logic
            $this->onStatusChanged($task->refresh(), $status);

            return $task->load([
                'project',
                'assignedTo',
                'createdBy'
            ]);
        });
    }

    public function assignTask(string $id, ?string $userId): Task
    {
        return $this->taskRepository->assignTask($id, $userId);
    }

    public function logTime(string $id, float $hours): Task
    {
        if ($hours <= 0) {
            throw new \InvalidArgumentException("Hours must be greater than 0");
        }

        return $this->taskRepository->logTime($id, $hours);
    }

    public function updateProgress(string $id, int $percentage): Task
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new \InvalidArgumentException("Progress percentage must be between 0 and 100");
        }

        return DB::transaction(function () use ($id, $percentage) {
            $task = $this->taskRepository->updateProgress($id, $percentage);

            // Auto-update status based on progress
            if ($percentage === 100 && $task->status !== TaskStatus::COMPLETED) {
                $task->update(['status' => TaskStatus::COMPLETED, 'completed_at' => now()]);
            } elseif ($percentage > 0 && $task->status === TaskStatus::NOT_STARTED) {
                $task->update(['status' => TaskStatus::IN_PROGRESS]);
            }

            // Update parent task progress
            if ($task->parent_task_id) {
                $task->parentTask->updateCalculatedProgress();
            }

            // Update project progress
            $task->project->updateCalculatedProgress();

            return $task;
        });
    }

    public function getProjectTasks(string $projectId, array $filters = []): Collection
    {
        $filters['project_id'] = $projectId;
        return $this->taskRepository->getByFilters($filters, [
            'assignedTo',
            'createdBy',
            'phase',
            'parentTask'
        ]);
    }

    public function getUserTasks(string $userId, array $filters = []): Collection
    {
        $filters['assigned_to_id'] = $userId;
        return $this->taskRepository->getByFilters($filters, [
            'project',
            'phase',
            'parentTask',
            'createdBy'
        ]);
    }

    public function getOverdueTasks(?string $projectId = null): Collection
    {
        $tasks = $this->taskRepository->getOverdueTasks([
            'project',
            'assignedTo',
            'phase'
        ]);

        if ($projectId) {
            return $tasks->where('project_id', $projectId);
        }

        return $tasks;
    }

    public function getTasksDueSoon(int $days = 7, ?string $projectId = null): Collection
    {
        $tasks = $this->taskRepository->getTasksDueSoon($days, [
            'project',
            'assignedTo',
            'phase'
        ]);

        if ($projectId) {
            return $tasks->where('project_id', $projectId);
        }

        return $tasks;
    }

    public function searchTasks(string $query, array $filters = []): Collection
    {
        if (isset($filters['search'])) {
            $filters['search'] = $query . ' ' . $filters['search'];
        } else {
            $filters['search'] = $query;
        }

        return $this->taskRepository->getByFilters($filters, [
            'project',
            'assignedTo',
            'phase',
            'parentTask'
        ]);
    }

    public function getTaskStatistics(?string $projectId = null): array
    {
        return $this->taskRepository->getStatistics($projectId);
    }

    public function getTaskHierarchy(string $taskId): Collection
    {
        return $this->taskRepository->getTaskHierarchy($taskId, [
            'assignedTo',
            'subTasks.assignedTo',
            'parentTask'
        ]);
    }

    public function addTaskDependency(string $taskId, string $dependsOnTaskId, string $dependencyType = 'finish_to_start', int $lagDays = 0): TaskDependency
    {
        return DB::transaction(function () use ($taskId, $dependsOnTaskId, $dependencyType, $lagDays) {
            $dependency = TaskDependency::create([
                'task_id' => $taskId,
                'depends_on_task_id' => $dependsOnTaskId,
                'dependency_type' => $dependencyType,
                'lag_days' => $lagDays,
            ]);

            // Validate the dependency
            $errors = $dependency->validateDependency();
            if (!empty($errors)) {
                throw new \InvalidArgumentException('Invalid dependency: ' . implode(', ', $errors));
            }

            return $dependency->load(['task', 'prerequisiteTask']);
        });
    }

    public function removeTaskDependency(string $taskId, string $dependsOnTaskId): bool
    {
        return TaskDependency::where('task_id', $taskId)
            ->where('depends_on_task_id', $dependsOnTaskId)
            ->delete() > 0;
    }

    public function getBlockedTasks(?string $projectId = null): Collection
    {
        $filters = ['project_id' => $projectId];
        $tasks = $this->taskRepository->getByFilters($filters, ['dependencies.prerequisiteTask']);

        return $tasks->filter(function ($task) {
            return $task->hasBlockingDependencies();
        });
    }

    public function getCriticalPath(string $projectId): Collection
    {
        return $this->taskRepository->getCriticalPathTasks($projectId, [
            'dependencies.prerequisiteTask',
            'dependentTasks.task',
            'assignedTo'
        ]);
    }

    public function duplicateTask(string $id, array $overrides = []): Task
    {
        $originalTask = $this->getTask($id);
        
        $taskData = array_merge([
            'name' => $originalTask->name . ' (Copy)',
            'description' => $originalTask->description,
            'project_id' => $originalTask->project_id,
            'phase_id' => $originalTask->phase_id,
            'parent_task_id' => $originalTask->parent_task_id,
            'task_type' => $originalTask->task_type->value,
            'priority' => $originalTask->priority->value,
            'status' => TaskStatus::NOT_STARTED->value,
            'assigned_to_id' => null, // Don't copy assignment
            'created_by_id' => auth()->id(),
            'estimated_hours' => $originalTask->estimated_hours,
            'start_date' => null,
            'due_date' => null,
            'task_order' => $originalTask->task_order + 1,
            'metadata' => $originalTask->metadata,
        ], $overrides);

        return $this->createTask(CreateTaskDTO::fromArray($taskData));
    }

    public function bulkUpdateTasks(array $taskIds, array $updates): int
    {
        return DB::transaction(function () use ($taskIds, $updates) {
            $updatedCount = 0;

            foreach ($taskIds as $taskId) {
                try {
                    $this->updateTask($taskId, UpdateTaskDTO::fromArray($updates));
                    $updatedCount++;
                } catch (\Exception $e) {
                    // Log error but continue with other tasks
                    logger()->warning("Failed to update task {$taskId}: " . $e->getMessage());
                }
            }

            return $updatedCount;
        });
    }

    /**
     * Handle status change events
     */
    protected function onStatusChanged(Task $task, TaskStatus $newStatus): void
    {
        switch ($newStatus) {
            case TaskStatus::COMPLETED:
                // Check if all sibling tasks are completed to update parent
                if ($task->parent_task_id) {
                    $parentTask = $task->parentTask;
                    $siblingTasks = $parentTask->subTasks;
                    
                    if ($siblingTasks->every(fn($t) => $t->status === TaskStatus::COMPLETED)) {
                        $parentTask->update(['status' => TaskStatus::COMPLETED, 'completed_at' => now()]);
                    }
                }
                break;

            case TaskStatus::IN_PROGRESS:
                // Auto-set start date if not set
                if (!$task->start_date) {
                    $task->update(['start_date' => now()->toDateString()]);
                }
                break;
        }

        // Update project progress after any status change
        $task->project->updateCalculatedProgress();
    }

    /**
     * Reorder sibling tasks to maintain proper ordering
     */
    protected function reorderSiblingTasks(string $parentTaskId): void
    {
        $siblings = Task::where('parent_task_id', $parentTaskId)
            ->orderBy('task_order')
            ->orderBy('created_at')
            ->get();

        $order = 1;
        foreach ($siblings as $sibling) {
            $sibling->update(['task_order' => $order++]);
        }
    }
}