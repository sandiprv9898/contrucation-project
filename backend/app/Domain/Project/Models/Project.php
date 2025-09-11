<?php

namespace App\Domain\Project\Models;

use App\Domain\Project\Enums\ProjectPriority;
use App\Domain\Project\Enums\ProjectStatus;
use App\Domain\Project\Enums\ProjectType;
use App\Domain\User\Models\Company;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'priority',
        'project_type',
        'client_company_id',
        'project_manager_id',
        'start_date',
        'end_date',
        'planned_budget',
        'actual_budget',
        'progress_percentage',
        'address',
        'coordinates',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'planned_budget' => 'decimal:2',
        'actual_budget' => 'decimal:2',
        'progress_percentage' => 'integer',
        'metadata' => 'array',
        'coordinates' => 'array',
        'status' => ProjectStatus::class,
        'priority' => ProjectPriority::class,
        'project_type' => ProjectType::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'client_company_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class)->orderBy('phase_order');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(\App\Domain\Task\Models\Task::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', ProjectStatus::ACTIVE);
    }

    public function scopeByStatus($query, ProjectStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, ProjectPriority $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCompany($query, string $companyId)
    {
        return $query->where('client_company_id', $companyId);
    }

    public function scopeByManager($query, string $managerId)
    {
        return $query->where('project_manager_id', $managerId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('end_date', '<', now())
                    ->whereNotIn('status', [ProjectStatus::COMPLETED, ProjectStatus::CANCELLED]);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
    }

    // Business Logic Methods
    public function calculateProgress(): int
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return 0;
        }
        
        // Use TaskStatus enum for accurate status checking
        $completedTasks = $this->tasks()->where('status', \App\Domain\Task\Enums\TaskStatus::COMPLETED->value)->count();
        return round(($completedTasks / $totalTasks) * 100);
    }

    public function updateCalculatedProgress(): void
    {
        $calculatedProgress = $this->calculateProgress();
        $this->update(['progress_percentage' => $calculatedProgress]);
    }

    public function isOverBudget(): bool
    {
        return $this->actual_budget > $this->planned_budget;
    }

    public function isOverdue(): bool
    {
        return $this->end_date && 
               $this->end_date->isPast() && 
               !in_array($this->status, [ProjectStatus::COMPLETED, ProjectStatus::CANCELLED]);
    }

    public function getBudgetVariance(): float
    {
        if (!$this->planned_budget) {
            return 0;
        }
        
        return $this->actual_budget - $this->planned_budget;
    }

    public function getBudgetVariancePercentage(): float
    {
        if (!$this->planned_budget) {
            return 0;
        }
        
        return (($this->actual_budget - $this->planned_budget) / $this->planned_budget) * 100;
    }

    public function getDurationInDays(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }
        
        return $this->start_date->diffInDays($this->end_date);
    }

    public function getTimeElapsedPercentage(): float
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        $totalDuration = $this->start_date->diffInDays($this->end_date);
        if ($totalDuration === 0) {
            return 100;
        }

        $elapsed = $this->start_date->diffInDays(now());
        return min(($elapsed / $totalDuration) * 100, 100);
    }

    public function canBeDeleted(): bool
    {
        return !in_array($this->status, [ProjectStatus::ACTIVE]);
    }

    public function canBeEdited(): bool
    {
        return !in_array($this->status, [ProjectStatus::COMPLETED, ProjectStatus::CANCELLED]);
    }

    // Status transition methods
    public function activate(): bool
    {
        if ($this->status === ProjectStatus::DRAFT || $this->status === ProjectStatus::ON_HOLD) {
            return $this->update(['status' => ProjectStatus::ACTIVE]);
        }
        
        return false;
    }

    public function putOnHold(): bool
    {
        if ($this->status === ProjectStatus::ACTIVE) {
            return $this->update(['status' => ProjectStatus::ON_HOLD]);
        }
        
        return false;
    }

    public function complete(): bool
    {
        if ($this->status === ProjectStatus::ACTIVE) {
            return $this->update([
                'status' => ProjectStatus::COMPLETED,
                'progress_percentage' => 100
            ]);
        }
        
        return false;
    }

    public function cancel(): bool
    {
        if (!in_array($this->status, [ProjectStatus::COMPLETED])) {
            return $this->update(['status' => ProjectStatus::CANCELLED]);
        }
        
        return false;
    }
}