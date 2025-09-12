<?php

namespace App\Domain\Task\Repositories;

use App\Domain\Task\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class TaskRepository implements TaskRepositoryInterface
{
    public function find(string $id): ?Task
    {
        return Task::find($id);
    }
    
    public function findAll(): Collection
    {
        return Task::all();
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
}