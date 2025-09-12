<?php

namespace App\Domain\Task\Repositories;

use App\Domain\Task\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function find(string $id): ?Task;
    
    public function findAll(): Collection;
    
    public function create(array $data): Task;
    
    public function update(string $id, array $data): Task;
    
    public function delete(string $id): bool;
    
    public function getProjectTasks(string $projectId, array $filters = []): Collection;
    
    public function getByAssignee(string $userId): Collection;
    
    public function getOverdue(): Collection;
    
    public function getDueSoon(int $days = 7): Collection;
    
    public function bulkUpdate(array $updates): array;
}