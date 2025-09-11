<?php

namespace App\Domain\Task\Repositories\Contracts;

use App\Domain\Task\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    /**
     * Find task by ID with optional relationships
     */
    public function findById(string $id, array $with = []): ?Task;

    /**
     * Get all tasks with pagination and filtering
     */
    public function paginate(
        int $perPage = 50,
        array $filters = [],
        string $sortBy = 'created_at',
        string $sortDirection = 'desc',
        array $with = []
    ): LengthAwarePaginator;

    /**
     * Create a new task
     */
    public function create(array $data): Task;

    /**
     * Update task by ID
     */
    public function update(string $id, array $data): Task;

    /**
     * Delete task by ID (soft delete)
     */
    public function delete(string $id): bool;

    /**
     * Get tasks by project ID
     */
    public function getByProjectId(string $projectId, array $with = []): Collection;

    /**
     * Get tasks by assignee ID
     */
    public function getByAssigneeId(string $assigneeId, array $with = []): Collection;

    /**
     * Get tasks by status
     */
    public function getByStatus(string $status, array $with = []): Collection;

    /**
     * Get tasks by priority
     */
    public function getByPriority(string $priority, array $with = []): Collection;

    /**
     * Get tasks by phase ID
     */
    public function getByPhaseId(string $phaseId, array $with = []): Collection;

    /**
     * Get subtasks by parent task ID
     */
    public function getSubTasks(string $parentTaskId, array $with = []): Collection;

    /**
     * Get top-level tasks (no parent)
     */
    public function getTopLevelTasks(string $projectId, array $with = []): Collection;

    /**
     * Search tasks by name or description
     */
    public function search(string $query, array $with = []): Collection;

    /**
     * Get overdue tasks
     */
    public function getOverdueTasks(array $with = []): Collection;

    /**
     * Get tasks due soon
     */
    public function getTasksDueSoon(int $days = 7, array $with = []): Collection;

    /**
     * Get completed tasks
     */
    public function getCompletedTasks(string $projectId = null, array $with = []): Collection;

    /**
     * Get task statistics
     */
    public function getStatistics(string $projectId = null): array;

    /**
     * Get tasks within date range
     */
    public function getTasksInDateRange(
        \Carbon\Carbon $startDate,
        \Carbon\Carbon $endDate,
        array $with = []
    ): Collection;

    /**
     * Update task progress
     */
    public function updateProgress(string $id, int $percentage): Task;

    /**
     * Update task status
     */
    public function updateStatus(string $id, string $status): Task;

    /**
     * Assign task to user
     */
    public function assignTask(string $id, string $userId): Task;

    /**
     * Log time for task
     */
    public function logTime(string $id, float $hours): Task;

    /**
     * Get tasks by multiple filters (advanced filtering)
     */
    public function getByFilters(array $filters, array $with = []): Collection;

    /**
     * Get task hierarchy (parent and all children)
     */
    public function getTaskHierarchy(string $taskId, array $with = []): Collection;

    /**
     * Get critical path tasks for a project
     */
    public function getCriticalPathTasks(string $projectId, array $with = []): Collection;
}