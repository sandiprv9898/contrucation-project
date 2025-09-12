<?php

namespace App\Domain\Project\Repositories;

use App\Domain\Project\Models\Project;
use App\Domain\Project\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function find(string $id): ?Project
    {
        return Project::find($id);
    }
    
    public function findById(string $id, array $with = []): ?Project
    {
        return Project::with($with)->find($id);
    }
    
    public function findAll(): Collection
    {
        return Project::all();
    }
    
    public function create(array $data): Project
    {
        return Project::create($data);
    }
    
    public function update(string $id, array $data): Project
    {
        $project = $this->find($id);
        $project->update($data);
        return $project->fresh();
    }
    
    public function delete(string $id): bool
    {
        $project = $this->find($id);
        return $project ? $project->delete() : false;
    }
    
    public function getByManager(string $managerId): Collection
    {
        return Project::where('manager_id', $managerId)->get();
    }
    
    public function getByManagerId(string $managerId, array $with = []): Collection
    {
        return Project::with($with)->where('manager_id', $managerId)->get();
    }
    
    public function getByCompanyId(string $companyId, array $with = []): Collection
    {
        return Project::with($with)->where('company_id', $companyId)->get();
    }
    
    public function getByStatus(string $status, array $with = []): Collection
    {
        return Project::with($with)->where('status', $status)->get();
    }
    
    public function search(string $query, array $with = []): Collection
    {
        return Project::with($with)->where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%");
        })->get();
    }
    
    
    public function paginate(
        int $perPage = 50,
        array $filters = [],
        string $sortBy = 'created_at',
        string $sortDirection = 'desc',
        array $with = []
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {
        $query = Project::with($with);
        
        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }
        
        return $query->orderBy($sortBy, $sortDirection)->paginate($perPage);
    }
    
    public function getByCompany(string $companyId): Collection
    {
        return Project::where('company_id', $companyId)->get();
    }
    
    public function getOverdue(): Collection
    {
        return Project::where('end_date', '<', now())
                     ->where('status', '!=', 'completed')
                     ->get();
    }
    
    
    public function getOverdueProjects(array $with = []): Collection
    {
        return Project::with($with)->where('end_date', '<', now())
                     ->where('status', '!=', 'completed')
                     ->get();
    }
    
    public function getProjectsInDateRange(
        \Carbon\Carbon $startDate,
        \Carbon\Carbon $endDate,
        array $with = []
    ): Collection {
        return Project::with($with)
                     ->whereBetween('start_date', [$startDate, $endDate])
                     ->get();
    }
    
    public function updateProgress(string $id, int $percentage): Project
    {
        $project = $this->findById($id);
        $project->update(['progress_percentage' => $percentage]);
        return $project->fresh();
    }
    
    public function updateBudget(string $id, float $actualBudget): Project
    {
        $project = $this->findById($id);
        $project->update(['actual_budget' => $actualBudget]);
        return $project->fresh();
    }
    public function getStatistics(): array
    {
        $total = Project::count();
        $active = Project::where('status', 'active')->count();
        $completed = Project::where('status', 'completed')->count();
        $overdue = $this->getOverdue()->count();
        
        return [
            'total' => $total,
            'active' => $active,
            'completed' => $completed,
            'overdue' => $overdue,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0
        ];
    }
}