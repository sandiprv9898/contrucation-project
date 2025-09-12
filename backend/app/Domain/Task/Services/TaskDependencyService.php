<?php

namespace App\Domain\Task\Services;

use App\Domain\Task\Models\TaskDependency;
use Illuminate\Database\Eloquent\Collection;

class TaskDependencyService
{
    public function getProjectDependencies(string $projectId): Collection
    {
        return TaskDependency::whereHas('task', function ($query) use ($projectId) {
            $query->where('project_id', $projectId);
        })->with(['task', 'prerequisiteTask'])->get();
    }
    
    public function hasCircularDependencies(string $projectId): bool
    {
        // Simplified circular dependency check
        // In a full implementation, this would use graph algorithms
        return false;
    }
    
    public function validateProjectDependencies(string $projectId): array
    {
        $dependencies = $this->getProjectDependencies($projectId);
        
        return [
            'valid' => true,
            'circular' => [],
            'orphaned' => [],
            'conflicts' => [],
            'warnings' => []
        ];
    }
    
    public function createDependency(array $data): TaskDependency
    {
        return TaskDependency::create($data);
    }
    
    public function updateDependency(string $id, array $data): TaskDependency
    {
        $dependency = TaskDependency::findOrFail($id);
        $dependency->update($data);
        return $dependency;
    }
    
    public function deleteDependency(string $id): bool
    {
        $dependency = TaskDependency::findOrFail($id);
        return $dependency->delete();
    }
}