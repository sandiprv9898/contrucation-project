<?php

namespace App\Domain\Project\Repositories;

use App\Domain\Project\Models\Project;
use Illuminate\Database\Eloquent\Collection;

interface ProjectRepositoryInterface
{
    public function find(string $id): ?Project;
    
    public function findAll(): Collection;
    
    public function create(array $data): Project;
    
    public function update(string $id, array $data): Project;
    
    public function delete(string $id): bool;
    
    public function getByManager(string $managerId): Collection;
    
    public function getByCompany(string $companyId): Collection;
    
    public function getOverdue(): Collection;
    
    public function search(string $query): Collection;
    
    public function getStatistics(): array;
}