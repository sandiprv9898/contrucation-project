<?php

namespace App\Domain\Project\Repositories;

use App\Domain\Project\Models\Project;
use App\Domain\Project\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function findById(string $id, array $with = []): ?Project
    {
        $query = Project::where('id', $id);

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
        $query = Project::query();

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

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function update(string $id, array $data): Project
    {
        $project = $this->findById($id);
        
        if (!$project) {
            throw new ModelNotFoundException("Project with ID {$id} not found");
        }

        $project->update($data);
        return $project->refresh();
    }

    public function delete(string $id): bool
    {
        $project = $this->findById($id);
        
        if (!$project) {
            throw new ModelNotFoundException("Project with ID {$id} not found");
        }

        return $project->delete();
    }

    public function getByCompanyId(string $companyId, array $with = []): Collection
    {
        $query = Project::byCompany($companyId);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getByManagerId(string $managerId, array $with = []): Collection
    {
        $query = Project::byManager($managerId);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getByStatus(string $status, array $with = []): Collection
    {
        $query = Project::where('status', $status);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function search(string $query, array $with = []): Collection
    {
        $searchQuery = Project::where('name', 'ILIKE', "%{$query}%")
            ->orWhere('description', 'ILIKE', "%{$query}%");

        if (!empty($with)) {
            $searchQuery->with($with);
        }

        return $searchQuery->get();
    }

    public function getStatistics(): array
    {
        $total = Project::count();
        $active = Project::where('status', 'active')->count();
        $completed = Project::where('status', 'completed')->count();
        $overdue = Project::overdue()->count();
        $onHold = Project::where('status', 'on_hold')->count();

        $totalBudget = Project::sum('planned_budget');
        $spentBudget = Project::sum('actual_budget');

        $averageProgress = Project::avg('progress_percentage');

        return [
            'total_projects' => $total,
            'active_projects' => $active,
            'completed_projects' => $completed,
            'overdue_projects' => $overdue,
            'on_hold_projects' => $onHold,
            'total_budget' => $totalBudget,
            'spent_budget' => $spentBudget,
            'average_progress' => round($averageProgress, 2),
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'budget_utilization' => $totalBudget > 0 ? round(($spentBudget / $totalBudget) * 100, 2) : 0,
        ];
    }

    public function getOverdueProjects(array $with = []): Collection
    {
        $query = Project::overdue();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function getProjectsInDateRange(
        \Carbon\Carbon $startDate,
        \Carbon\Carbon $endDate,
        array $with = []
    ): Collection {
        $query = Project::inDateRange($startDate, $endDate);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    public function updateProgress(string $id, int $percentage): Project
    {
        return $this->update($id, ['progress_percentage' => $percentage]);
    }

    public function updateBudget(string $id, float $actualBudget): Project
    {
        return $this->update($id, ['actual_budget' => $actualBudget]);
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

        if (isset($filters['project_type'])) {
            $query->where('project_type', $filters['project_type']);
        }

        if (isset($filters['client_company_id'])) {
            $query->where('client_company_id', $filters['client_company_id']);
        }

        if (isset($filters['project_manager_id'])) {
            $query->where('project_manager_id', $filters['project_manager_id']);
        }

        if (isset($filters['start_date_from'])) {
            $query->where('start_date', '>=', $filters['start_date_from']);
        }

        if (isset($filters['start_date_to'])) {
            $query->where('start_date', '<=', $filters['start_date_to']);
        }

        if (isset($filters['end_date_from'])) {
            $query->where('end_date', '>=', $filters['end_date_from']);
        }

        if (isset($filters['end_date_to'])) {
            $query->where('end_date', '<=', $filters['end_date_to']);
        }

        if (isset($filters['budget_min'])) {
            $query->where('planned_budget', '>=', $filters['budget_min']);
        }

        if (isset($filters['budget_max'])) {
            $query->where('planned_budget', '<=', $filters['budget_max']);
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
    }
}