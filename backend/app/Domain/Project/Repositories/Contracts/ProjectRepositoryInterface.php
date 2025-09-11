<?php

namespace App\Domain\Project\Repositories\Contracts;

use App\Domain\Project\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProjectRepositoryInterface
{
    /**
     * Find project by ID with optional relationships
     */
    public function findById(string $id, array $with = []): ?Project;

    /**
     * Get all projects with pagination and filtering
     */
    public function paginate(
        int $perPage = 50,
        array $filters = [],
        string $sortBy = 'created_at',
        string $sortDirection = 'desc',
        array $with = []
    ): LengthAwarePaginator;

    /**
     * Create a new project
     */
    public function create(array $data): Project;

    /**
     * Update project by ID
     */
    public function update(string $id, array $data): Project;

    /**
     * Delete project by ID (soft delete)
     */
    public function delete(string $id): bool;

    /**
     * Get projects by company ID
     */
    public function getByCompanyId(string $companyId, array $with = []): Collection;

    /**
     * Get projects by manager ID
     */
    public function getByManagerId(string $managerId, array $with = []): Collection;

    /**
     * Get projects by status
     */
    public function getByStatus(string $status, array $with = []): Collection;

    /**
     * Search projects by name or description
     */
    public function search(string $query, array $with = []): Collection;

    /**
     * Get project statistics
     */
    public function getStatistics(): array;

    /**
     * Get overdue projects
     */
    public function getOverdueProjects(array $with = []): Collection;

    /**
     * Get projects within date range
     */
    public function getProjectsInDateRange(
        \Carbon\Carbon $startDate,
        \Carbon\Carbon $endDate,
        array $with = []
    ): Collection;

    /**
     * Update project progress
     */
    public function updateProgress(string $id, int $percentage): Project;

    /**
     * Update project budget
     */
    public function updateBudget(string $id, float $actualBudget): Project;
}