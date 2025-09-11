<?php

namespace App\Http\Resources\Project;

use App\Http\Resources\User\CompanyResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectListResource extends JsonResource
{
    /**
     * Transform the resource into an array for list views.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->when(
                strlen($this->description) <= 100,
                $this->description,
                substr($this->description, 0, 100) . '...'
            ),
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'priority' => [
                'value' => $this->priority->value,
                'label' => $this->priority->label(),
                'color' => $this->priority->color(),
                'weight' => $this->priority->weight(),
            ],
            'project_type' => [
                'value' => $this->project_type->value,
                'label' => $this->project_type->label(),
            ],
            'client' => [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ],
            'manager' => [
                'id' => $this->manager->id,
                'name' => $this->manager->name,
                'email' => $this->manager->email,
            ],
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'planned_budget' => $this->planned_budget,
            'actual_budget' => $this->actual_budget,
            'progress_percentage' => $this->progress_percentage,
            
            // Quick status indicators
            'is_overdue' => $this->isOverdue(),
            'is_over_budget' => $this->isOverBudget(),
            'budget_variance_percentage' => $this->getBudgetVariancePercentage(),
            
            // Counts for quick overview
            'phases_count' => $this->phases_count ?? $this->phases()->count(),
            'tasks_count' => $this->tasks_count ?? $this->tasks()->count(),
            'completed_tasks_count' => $this->tasks()->where('status', 'completed')->count(),
            'milestones_count' => $this->milestones_count ?? $this->milestones()->count(),
            
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}