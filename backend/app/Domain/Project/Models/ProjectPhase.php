<?php

namespace App\Domain\Project\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectPhase extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'phase_order',
        'status',
        'start_date',
        'end_date',
        'estimated_duration_days',
        'actual_duration_days',
        'budget_allocation',
        'actual_cost',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'phase_order' => 'integer',
        'estimated_duration_days' => 'integer',
        'actual_duration_days' => 'integer',
        'budget_allocation' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'phase_id');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('phase_order');
    }

    // Business Logic Methods
    public function calculateActualDuration(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        return $this->start_date->diffInDays($this->end_date);
    }

    public function updateActualDuration(): void
    {
        $actualDuration = $this->calculateActualDuration();
        if ($actualDuration !== null) {
            $this->update(['actual_duration_days' => $actualDuration]);
        }
    }

    public function isOverBudget(): bool
    {
        return $this->budget_allocation && $this->actual_cost > $this->budget_allocation;
    }

    public function isOverdue(): bool
    {
        return $this->end_date && 
               $this->end_date->isPast() && 
               $this->status !== 'completed';
    }

    public function getProgressPercentage(): float
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->tasks()->where('status', 'completed')->count();
        return ($completedTasks / $totalTasks) * 100;
    }

    public function canBeDeleted(): bool
    {
        return $this->tasks()->count() === 0;
    }

    public function start(): bool
    {
        if ($this->status === 'pending') {
            return $this->update([
                'status' => 'in_progress',
                'start_date' => now()
            ]);
        }

        return false;
    }

    public function complete(): bool
    {
        if ($this->status === 'in_progress') {
            return $this->update([
                'status' => 'completed',
                'end_date' => now()
            ]);
        }

        return false;
    }
}