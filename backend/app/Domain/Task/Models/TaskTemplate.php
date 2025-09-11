<?php

namespace App\Domain\Task\Models;

use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'category',
        'task_type',
        'estimated_hours',
        'default_priority',
        'required_skills',
        'safety_requirements',
        'materials_needed',
        'checklist',
        'is_active',
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'required_skills' => 'array',
        'safety_requirements' => 'array',
        'materials_needed' => 'array',
        'checklist' => 'array',
        'is_active' => 'boolean',
        'default_priority' => TaskPriority::class,
        'task_type' => TaskType::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByType($query, TaskType $type)
    {
        return $query->where('task_type', $type);
    }

    public function scopeByPriority($query, TaskPriority $priority)
    {
        return $query->where('default_priority', $priority);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ILIKE', "%{$search}%")
              ->orWhere('description', 'ILIKE', "%{$search}%")
              ->orWhere('category', 'ILIKE', "%{$search}%");
        });
    }

    // Business Logic Methods
    public function createTaskFromTemplate(array $overrides = []): array
    {
        $taskData = [
            'name' => $this->name,
            'description' => $this->description,
            'task_type' => $this->task_type,
            'priority' => $this->default_priority,
            'estimated_hours' => $this->estimated_hours,
            'metadata' => [
                'template_id' => $this->id,
                'required_skills' => $this->required_skills,
                'safety_requirements' => $this->safety_requirements,
                'materials_needed' => $this->materials_needed,
                'checklist' => $this->checklist,
            ],
        ];

        // Apply overrides
        return array_merge($taskData, $overrides);
    }

    public function hasSkillRequirement(string $skill): bool
    {
        return in_array($skill, $this->required_skills ?? []);
    }

    public function hasSafetyRequirement(string $requirement): bool
    {
        return in_array($requirement, $this->safety_requirements ?? []);
    }

    public function requiresMaterial(string $material): bool
    {
        return collect($this->materials_needed ?? [])
            ->pluck('name')
            ->contains($material);
    }

    public function getChecklistItems(): array
    {
        return $this->checklist ?? [];
    }

    public function getEstimatedDuration(): ?int
    {
        if (!$this->estimated_hours) {
            return null;
        }

        // Assuming 8 hours per day
        return ceil($this->estimated_hours / 8);
    }

    public function getSafetyLevel(): string
    {
        $safetyRequirements = count($this->safety_requirements ?? []);
        
        return match(true) {
            $safetyRequirements >= 5 => 'high',
            $safetyRequirements >= 3 => 'medium',
            $safetyRequirements >= 1 => 'low',
            default => 'minimal'
        };
    }

    public function getComplexityLevel(): string
    {
        $skillsCount = count($this->required_skills ?? []);
        $checklistCount = count($this->checklist ?? []);
        $materialsCount = count($this->materials_needed ?? []);
        
        $complexity = $skillsCount + ($checklistCount / 2) + ($materialsCount / 3);
        
        return match(true) {
            $complexity >= 10 => 'high',
            $complexity >= 5 => 'medium',
            $complexity >= 2 => 'low',
            default => 'simple'
        };
    }

    public static function getAvailableCategories(): array
    {
        return static::active()
            ->distinct('category')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    public static function getPopularTemplates(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        // For now, return most recently created active templates
        // In the future, this could be based on usage statistics
        return static::active()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}