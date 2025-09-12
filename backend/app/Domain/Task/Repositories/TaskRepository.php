<?php

namespace App\Domain\Task\Repositories;

use App\Domain\Task\Models\Task;
use App\Domain\Task\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class TaskRepository implements TaskRepositoryInterface
{
    public function find(string $id): ?Task
    {
        return Task::find($id);
    }
    
    public function findById(string $id, array $with = []): ?Task
    {
        $query = Task::query();
        
        if (!empty($with)) {
            $query->with($with);
        }
        
        return $query->find($id);
    }
    
    public function findAll(): Collection
    {
        return Task::all();
    }
    
    public function paginate(
        int $perPage = 50,
        array $filters = [],
        string $sortBy = 'created_at',
        string $sortDirection = 'desc',
        array $with = []
    ): LengthAwarePaginator {
        $query = Task::query();
        
        if (!empty($with)) {
            $query->with($with);
        }
        
        // Join with time logs to get active tracking information
        $query->leftJoin('task_time_logs', function($join) {
            $join->on('tasks.id', '=', 'task_time_logs.task_id')
                 ->where('task_time_logs.is_active', true)
                 ->whereNull('task_time_logs.end_time');
        });
        
        // Select distinct tasks to avoid duplicates from joins
        $query->select('tasks.*')
              ->selectRaw('CASE WHEN task_time_logs.is_active = 1 THEN 1 ELSE 0 END as has_active_timer');
        
        // Apply basic filters
        if (isset($filters['status'])) {
            $query->where('tasks.status', $filters['status']);
        }
        
        if (isset($filters['priority'])) {
            $query->where('tasks.priority', $filters['priority']);
        }
        
        if (isset($filters['assigned_to_id'])) {
            $query->where('tasks.assigned_to_id', $filters['assigned_to_id']);
        }
        
        if (isset($filters['project_id'])) {
            $query->where('tasks.project_id', $filters['project_id']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('tasks.name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('tasks.description', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        // Group by task ID to handle the join duplicates
        $query->groupBy('tasks.id');
        
        // Sort by active timer first, then by the requested sort
        $query->orderByRaw('has_active_timer DESC')
              ->orderBy('tasks.' . $sortBy, $sortDirection);
        
        return $query->paginate($perPage);
    }
    
    public function create(array $data): Task
    {
        return Task::create($data);
    }
    
    public function update(string $id, array $data): Task
    {
        $task = $this->find($id);
        $task->update($data);
        return $task->fresh();
    }
    
    public function delete(string $id): bool
    {
        $task = $this->find($id);
        return $task ? $task->delete() : false;
    }
    
    public function getProjectTasks(string $projectId, array $filters = []): Collection
    {
        $query = Task::where('project_id', $projectId);
        
        // Apply filters
        if (!empty($filters['status'])) {
            $query->whereIn('status', $filters['status']);
        }
        
        if (!empty($filters['priority'])) {
            $query->whereIn('priority', $filters['priority']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        if (!empty($filters['date_range'])) {
            $start = $filters['date_range']['start'];
            $end = $filters['date_range']['end'];
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_date', '<=', $start)
                         ->where('end_date', '>=', $end);
                  });
            });
        }
        
        if (isset($filters['show_completed']) && !$filters['show_completed']) {
            $query->where('status', '!=', 'completed');
        }
        
        return $query->orderBy('start_date')
                    ->orderBy('created_at')
                    ->get();
    }
    
    public function getByAssignee(string $userId): Collection
    {
        return Task::where('assigned_to', $userId)
                  ->orWhereJsonContains('assignees', $userId)
                  ->get();
    }
    
    public function getOverdue(): Collection
    {
        return Task::where('end_date', '<', now())
                  ->where('status', '!=', 'completed')
                  ->get();
    }
    
    public function getDueSoon(int $days = 7): Collection
    {
        $dueDate = now()->addDays($days);
        
        return Task::where('end_date', '<=', $dueDate)
                  ->where('end_date', '>=', now())
                  ->where('status', '!=', 'completed')
                  ->get();
    }
    
    public function bulkUpdate(array $updates): array
    {
        $updated = 0;
        $failed = [];
        
        foreach ($updates as $update) {
            try {
                if (isset($update['id']) && isset($update['changes'])) {
                    $this->update($update['id'], $update['changes']);
                    $updated++;
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'id' => $update['id'] ?? null,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'updated' => $updated,
            'failed' => $failed
        ];
    }
    
    // Additional required interface methods (stubs)
    public function getByProjectId(string $projectId, array $with = []): Collection
    {
        return $this->getProjectTasks($projectId);
    }
    
    public function getByAssigneeId(string $assigneeId, array $with = []): Collection
    {
        return $this->getByAssignee($assigneeId);
    }
    
    public function getByStatus(string $status, array $with = []): Collection
    {
        return Task::where('status', $status)->with($with)->get();
    }
    
    public function getByPriority(string $priority, array $with = []): Collection
    {
        return Task::where('priority', $priority)->with($with)->get();
    }
    
    public function getByPhaseId(string $phaseId, array $with = []): Collection
    {
        return Task::where('phase_id', $phaseId)->with($with)->get();
    }
    
    public function getSubTasks(string $parentTaskId, array $with = []): Collection
    {
        return Task::where('parent_task_id', $parentTaskId)->with($with)->get();
    }
    
    public function getTopLevelTasks(string $projectId, array $with = []): Collection
    {
        return Task::where('project_id', $projectId)
                  ->whereNull('parent_task_id')
                  ->with($with)
                  ->get();
    }
    
    public function search(string $query, array $with = []): Collection
    {
        return Task::where('name', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%')
                  ->with($with)
                  ->get();
    }
    
    public function getOverdueTasks(array $with = []): Collection
    {
        return $this->getOverdue();
    }
    
    public function getTasksDueSoon(int $days = 7, array $with = []): Collection
    {
        return $this->getDueSoon($days);
    }
    
    public function getCompletedTasks(string $projectId = null, array $with = []): Collection
    {
        $query = Task::where('status', 'completed');
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        return $query->with($with)->get();
    }
    
    public function getStatistics(string $projectId = null): array
    {
        $query = Task::query();
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        return [
            'total' => $query->count(),
            'completed' => $query->clone()->where('status', 'completed')->count(),
            'in_progress' => $query->clone()->where('status', 'in_progress')->count(),
            'pending' => $query->clone()->where('status', 'pending')->count(),
        ];
    }
    
    public function getTasksInDateRange(Carbon $startDate, Carbon $endDate, array $with = []): Collection
    {
        return Task::whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->with($with)
                  ->get();
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
        $task = $this->find($id);
        $currentHours = $task->actual_hours ?? 0;
        return $this->update($id, ['actual_hours' => $currentHours + $hours]);
    }
    
    public function getByFilters(array $filters, array $with = []): Collection
    {
        $query = Task::query();
        
        foreach ($filters as $key => $value) {
            if (in_array($key, ['status', 'priority', 'project_id', 'assigned_to_id'])) {
                $query->where($key, $value);
            }
        }
        
        return $query->with($with)->get();
    }
    
    public function getTaskHierarchy(string $taskId, array $with = []): Collection
    {
        $task = $this->findById($taskId, $with);
        $hierarchy = collect([$task]);
        
        // Get all subtasks recursively
        $subtasks = $this->getSubTasks($taskId, $with);
        foreach ($subtasks as $subtask) {
            $hierarchy = $hierarchy->merge($this->getTaskHierarchy($subtask->id, $with));
        }
        
        return $hierarchy;
    }
    
    public function getCriticalPathTasks(string $projectId, array $with = []): Collection
    {
        // Simplified implementation - just return high priority tasks
        return Task::where('project_id', $projectId)
                  ->where('priority', 'high')
                  ->with($with)
                  ->get();
    }
}