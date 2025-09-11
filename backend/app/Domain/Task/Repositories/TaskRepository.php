<?php

namespace App\Domain\Task\Repositories;

use App\Domain\Task\Models\Task;
use App\Domain\Task\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskRepository implements TaskRepositoryInterface
{
    public function findById(string $id, array $with = []): ?Task
    {
        $query = Task::where('id', $id);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->first();
    }

    public function paginate(
        int $perPage = 50,
        array $filters = [],
        string $sortBy = 'created_at',
        string $sortDirection = 'desc',
        array $with = []
    ): LengthAwarePaginator {
        $query = Task::query();

        // Apply relationships
        if (!empty($with)) {
            $query->with($with);
        }

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(string $id, array $data): Task
    {
        $task = $this->findById($id);
        
        if (!$task) {
            throw new ModelNotFoundException("Task with ID {$id} not found");
        }

        $task->update($data);
        return $task->refresh();
    }

    public function delete(string $id): bool
    {
        $task = $this->findById($id);
        
        if (!$task) {
            throw new ModelNotFoundException("Task with ID {$id} not found");
        }

        return $task->delete();
    }

    public function getByProjectId(string $projectId, array $with = []): Collection
    {
        $query = Task::byProject($projectId);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getByAssigneeId(string $assigneeId, array $with = []): Collection
    {
        $query = Task::byAssignee($assigneeId);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getByStatus(string $status, array $with = []): Collection
    {
        $query = Task::where('status', $status);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getByPriority(string $priority, array $with = []): Collection
    {
        $query = Task::where('priority', $priority);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getByPhaseId(string $phaseId, array $with = []): Collection
    {
        $query = Task::byPhase($phaseId);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getSubTasks(string $parentTaskId, array $with = []): Collection
    {
        $query = Task::where('parent_task_id', $parentTaskId);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->orderBy('task_order')->get();
    }

    public function getTopLevelTasks(string $projectId, array $with = []): Collection
    {
        $query = Task::topLevel()->byProject($projectId);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->orderBy('task_order')->get();
    }

    public function search(string $query, array $with = []): Collection
    {
        $searchQuery = Task::where('name', 'ILIKE', "%{$query}%")
            ->orWhere('description', 'ILIKE', "%{$query}%");

        if (!empty($with)) {
            $searchQuery->with($with);
        }

        return $searchQuery->get();
    }

    public function getOverdueTasks(array $with = []): Collection
    {
        $query = Task::overdue();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getTasksDueSoon(int $days = 7, array $with = []): Collection
    {
        $query = Task::dueSoon($days);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getCompletedTasks(string $projectId = null, array $with = []): Collection
    {
        $query = Task::completed();

        if ($projectId) {
            $query->byProject($projectId);
        }

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getStatistics(string $projectId = null): array
    {
        $query = Task::query();

        if ($projectId) {
            $query->byProject($projectId);
        }

        $total = $query->count();
        $notStarted = (clone $query)->where('status', 'not_started')->count();
        $inProgress = (clone $query)->where('status', 'in_progress')->count();
        $review = (clone $query)->where('status', 'review')->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();
        $overdue = (clone $query)->overdue()->count();

        $totalHours = (clone $query)->sum('estimated_hours');
        $actualHours = (clone $query)->sum('actual_hours');
        $averageProgress = (clone $query)->avg('progress_percentage');

        return [
            'total_tasks' => $total,
            'not_started_tasks' => $notStarted,
            'in_progress_tasks' => $inProgress,
            'review_tasks' => $review,
            'completed_tasks' => $completed,
            'cancelled_tasks' => $cancelled,
            'overdue_tasks' => $overdue,
            'total_estimated_hours' => $totalHours,
            'total_actual_hours' => $actualHours,
            'average_progress' => round($averageProgress, 2),
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'time_variance' => $actualHours - $totalHours,
            'time_variance_percentage' => $totalHours > 0 ? round((($actualHours - $totalHours) / $totalHours) * 100, 2) : 0,
        ];
    }

    public function getTasksInDateRange(
        \Carbon\Carbon $startDate,
        \Carbon\Carbon $endDate,
        array $with = []
    ): Collection {
        $query = Task::where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('due_date', [$startDate, $endDate]);
        });

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function updateProgress(string $id, int $percentage): Task
    {
        return $this->update($id, ['progress_percentage' => $percentage]);
    }

    public function updateStatus(string $id, string $status): Task
    {
        return $this->update($id, ['status' => $status]);
    }

    public function assignTask(string $id, string $userId): Task
    {
        return $this->update($id, ['assigned_to_id' => $userId]);
    }

    public function logTime(string $id, float $hours): Task
    {
        $task = $this->findById($id);
        
        if (!$task) {
            throw new ModelNotFoundException("Task with ID {$id} not found");
        }

        $newActualHours = $task->actual_hours + $hours;
        return $this->update($id, ['actual_hours' => $newActualHours]);
    }

    public function getByFilters(array $filters, array $with = []): Collection
    {
        $query = Task::query();

        if (!empty($with)) {
            $query->with($with);
        }

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    public function getTaskHierarchy(string $taskId, array $with = []): Collection
    {
        $task = $this->findById($taskId, $with);
        
        if (!$task) {
            return collect();
        }

        $hierarchy = collect([$task]);
        
        // Get all subtasks recursively
        $subtasks = $this->getAllSubTasksRecursively($task, $with);
        $hierarchy = $hierarchy->merge($subtasks);

        return $hierarchy;
    }

    public function getCriticalPathTasks(string $projectId, array $with = []): Collection
    {
        // This is a simplified critical path calculation
        // In a real implementation, you would use a more sophisticated algorithm
        $query = Task::byProject($projectId)
            ->where('priority', 'critical')
            ->orWhere('priority', 'high');

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->orderBy('due_date')->get();
    }

    /**
     * Apply filters to the query
     */
    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['task_type'])) {
            $query->where('task_type', $filters['task_type']);
        }

        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (isset($filters['phase_id'])) {
            $query->where('phase_id', $filters['phase_id']);
        }

        if (isset($filters['assigned_to_id'])) {
            $query->where('assigned_to_id', $filters['assigned_to_id']);
        }

        if (isset($filters['created_by_id'])) {
            $query->where('created_by_id', $filters['created_by_id']);
        }

        if (isset($filters['parent_task_id'])) {
            $query->where('parent_task_id', $filters['parent_task_id']);
        }

        if (isset($filters['start_date_from'])) {
            $query->where('start_date', '>=', $filters['start_date_from']);
        }

        if (isset($filters['start_date_to'])) {
            $query->where('start_date', '<=', $filters['start_date_to']);
        }

        if (isset($filters['due_date_from'])) {
            $query->where('due_date', '>=', $filters['due_date_from']);
        }

        if (isset($filters['due_date_to'])) {
            $query->where('due_date', '<=', $filters['due_date_to']);
        }

        if (isset($filters['hours_min'])) {
            $query->where('estimated_hours', '>=', $filters['hours_min']);
        }

        if (isset($filters['hours_max'])) {
            $query->where('estimated_hours', '<=', $filters['hours_max']);
        }

        if (isset($filters['progress_min'])) {
            $query->where('progress_percentage', '>=', $filters['progress_min']);
        }

        if (isset($filters['progress_max'])) {
            $query->where('progress_percentage', '<=', $filters['progress_max']);
        }

        if (isset($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('name', 'ILIKE', "%{$searchTerm}%")
                         ->orWhere('description', 'ILIKE', "%{$searchTerm}%");
            });
        }

        if (isset($filters['overdue']) && $filters['overdue']) {
            $query->overdue();
        }

        if (isset($filters['due_soon']) && $filters['due_soon']) {
            $query->dueSoon($filters['due_soon_days'] ?? 7);
        }

        if (isset($filters['top_level_only']) && $filters['top_level_only']) {
            $query->topLevel();
        }

        if (isset($filters['subtasks_only']) && $filters['subtasks_only']) {
            $query->subTasks();
        }
    }

    /**
     * Get all subtasks recursively
     */
    protected function getAllSubTasksRecursively(Task $task, array $with = []): Collection
    {
        $subtasks = collect();
        
        $directSubtasks = $this->getSubTasks($task->id, $with);
        
        foreach ($directSubtasks as $subtask) {
            $subtasks->push($subtask);
            $nestedSubtasks = $this->getAllSubTasksRecursively($subtask, $with);
            $subtasks = $subtasks->merge($nestedSubtasks);
        }
        
        return $subtasks;
    }
}