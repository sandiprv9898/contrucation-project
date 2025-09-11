<?php

namespace App\Domain\Project\Services;

use App\Domain\Project\DTOs\CreateProjectDTO;
use App\Domain\Project\DTOs\UpdateProjectDTO;
use App\Domain\Project\Enums\ProjectStatus;
use App\Domain\Project\Models\Project;
use App\Domain\Project\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository,
        private ProjectStatisticsService $statisticsService
    ) {}

    public function createProject(CreateProjectDTO $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = $this->projectRepository->create($data->toArray());

            // Create default phases
            $this->createDefaultPhases($project);

            return $project->load(['client', 'manager', 'phases']);
        });
    }

    public function updateProject(string $id, UpdateProjectDTO $data): Project
    {
        return DB::transaction(function () use ($id, $data) {
            $project = $this->projectRepository->update($id, $data->toArray());

            // Recalculate progress if needed
            if ($data->progressPercentage === null) {
                $project->updateCalculatedProgress();
            }

            return $project->load(['client', 'manager', 'phases']);
        });
    }

    public function deleteProject(string $id): bool
    {
        $project = $this->projectRepository->findById($id);

        if (!$project) {
            throw new ModelNotFoundException("Project with ID {$id} not found");
        }

        if (!$project->canBeDeleted()) {
            throw new \InvalidArgumentException("Cannot delete an active project");
        }

        return $this->projectRepository->delete($id);
    }

    public function getProject(string $id): Project
    {
        $project = $this->projectRepository->findById($id, [
            'client',
            'manager',
            'phases',
            'tasks.assignedTo',
            'milestones'
        ]);

        if (!$project) {
            throw new ModelNotFoundException("Project with ID {$id} not found");
        }

        return $project;
    }

    public function updateProjectStatus(string $id, ProjectStatus $status): Project
    {
        return DB::transaction(function () use ($id, $status) {
            $project = $this->projectRepository->findById($id);

            if (!$project) {
                throw new ModelNotFoundException("Project with ID {$id} not found");
            }

            $updated = match($status) {
                ProjectStatus::ACTIVE => $project->activate(),
                ProjectStatus::ON_HOLD => $project->putOnHold(),
                ProjectStatus::COMPLETED => $project->complete(),
                ProjectStatus::CANCELLED => $project->cancel(),
                default => $project->update(['status' => $status])
            };

            if (!$updated) {
                throw new \InvalidArgumentException("Cannot transition from {$project->status->value} to {$status->value}");
            }

            // Trigger status-specific business logic
            $this->onStatusChanged($project, $status);

            return $project->refresh();
        });
    }

    public function getProjectsByCompany(string $companyId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->projectRepository->getByCompanyId($companyId, [
            'client',
            'manager'
        ]);
    }

    public function getProjectsByManager(string $managerId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->projectRepository->getByManagerId($managerId, [
            'client',
            'manager'
        ]);
    }

    public function searchProjects(string $query): \Illuminate\Database\Eloquent\Collection
    {
        return $this->projectRepository->search($query, [
            'client',
            'manager'
        ]);
    }

    public function getOverdueProjects(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->projectRepository->getOverdueProjects([
            'client',
            'manager'
        ]);
    }

    public function getProjectsInDateRange(
        \Carbon\Carbon $startDate,
        \Carbon\Carbon $endDate
    ): \Illuminate\Database\Eloquent\Collection {
        return $this->projectRepository->getProjectsInDateRange($startDate, $endDate, [
            'client',
            'manager'
        ]);
    }

    public function updateProjectProgress(string $id): Project
    {
        $project = $this->projectRepository->findById($id);

        if (!$project) {
            throw new ModelNotFoundException("Project with ID {$id} not found");
        }

        $project->updateCalculatedProgress();

        return $project->refresh();
    }

    public function updateProjectBudget(string $id, float $actualBudget): Project
    {
        return $this->projectRepository->updateBudget($id, $actualBudget);
    }

    public function getProjectStatistics(): array
    {
        return $this->statisticsService->getOverallStatistics();
    }

    public function getProjectStatisticsByCompany(string $companyId): array
    {
        return $this->statisticsService->getStatisticsByCompany($companyId);
    }

    public function getProjectStatisticsByManager(string $managerId): array
    {
        return $this->statisticsService->getStatisticsByManager($managerId);
    }

    /**
     * Create default phases for a new project
     */
    private function createDefaultPhases(Project $project): void
    {
        $defaultPhases = [
            [
                'name' => 'Planning',
                'description' => 'Project planning and design phase',
                'phase_order' => 1,
                'status' => 'pending'
            ],
            [
                'name' => 'Execution',
                'description' => 'Main construction/implementation phase',
                'phase_order' => 2,
                'status' => 'pending'
            ],
            [
                'name' => 'Completion',
                'description' => 'Final inspections and project handover',
                'phase_order' => 3,
                'status' => 'pending'
            ]
        ];

        foreach ($defaultPhases as $phaseData) {
            $project->phases()->create($phaseData);
        }
    }

    /**
     * Handle status change business logic
     */
    private function onStatusChanged(Project $project, ProjectStatus $newStatus): void
    {
        match($newStatus) {
            ProjectStatus::ACTIVE => $this->onProjectActivated($project),
            ProjectStatus::COMPLETED => $this->onProjectCompleted($project),
            ProjectStatus::CANCELLED => $this->onProjectCancelled($project),
            ProjectStatus::ON_HOLD => $this->onProjectPutOnHold($project),
            default => null
        };
    }

    private function onProjectActivated(Project $project): void
    {
        // Logic when project becomes active
        // e.g., notify team members, start tracking, etc.
        
        // Set start date if not already set
        if (!$project->start_date) {
            $project->update(['start_date' => now()]);
        }
    }

    private function onProjectCompleted(Project $project): void
    {
        // Logic when project is completed
        // e.g., finalize budgets, generate reports, notify stakeholders
        
        // Complete all pending milestones
        $project->milestones()
                ->where('status', 'pending')
                ->update([
                    'status' => 'completed',
                    'completed_date' => now()
                ]);

        // Set actual end date
        if (!$project->end_date) {
            $project->update(['end_date' => now()]);
        }
    }

    private function onProjectCancelled(Project $project): void
    {
        // Logic when project is cancelled
        // e.g., cleanup resources, notify stakeholders, handle refunds
        
        // Cancel all pending milestones
        $project->milestones()
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);
    }

    private function onProjectPutOnHold(Project $project): void
    {
        // Logic when project is put on hold
        // e.g., pause tracking, notify team, preserve state
    }
}