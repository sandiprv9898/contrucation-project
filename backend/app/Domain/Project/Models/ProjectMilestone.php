<?php

namespace App\Domain\Project\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestone extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'target_date',
        'completed_date',
        'status',
        'milestone_type',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('milestone_type', $type);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('target_date', '>', now())
                    ->where('status', '!=', 'completed')
                    ->orderBy('target_date');
    }

    public function scopeOverdue($query)
    {
        return $query->where('target_date', '<', now())
                    ->where('status', '!=', 'completed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOrderByDate($query)
    {
        return $query->orderBy('target_date');
    }

    // Business Logic Methods
    public function isOverdue(): bool
    {
        return $this->target_date && 
               $this->target_date->isPast() && 
               $this->status !== 'completed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isUpcoming(): bool
    {
        return $this->target_date && 
               $this->target_date->isFuture() && 
               $this->status !== 'completed';
    }

    public function getDaysUntilTarget(): int
    {
        if (!$this->target_date) {
            return 0;
        }

        return now()->diffInDays($this->target_date, false);
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return $this->target_date->diffInDays(now());
    }

    public function complete(): bool
    {
        if ($this->status !== 'completed') {
            return $this->update([
                'status' => 'completed',
                'completed_date' => now()
            ]);
        }

        return false;
    }

    public function uncomplete(): bool
    {
        if ($this->status === 'completed') {
            return $this->update([
                'status' => 'pending',
                'completed_date' => null
            ]);
        }

        return false;
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'completed' => 'green',
            'pending' => $this->isOverdue() ? 'red' : 'blue',
            'cancelled' => 'gray',
            default => 'blue'
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'completed' => 'Completed',
            'pending' => $this->isOverdue() ? 'Overdue' : 'Pending',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }

    public function getTypeLabel(): string
    {
        return match($this->milestone_type) {
            'delivery' => 'Delivery',
            'payment' => 'Payment',
            'approval' => 'Approval',
            'inspection' => 'Inspection',
            'deadline' => 'Deadline',
            default => ucfirst($this->milestone_type)
        };
    }
}