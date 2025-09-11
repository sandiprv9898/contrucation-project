<?php

namespace App\Http\Resources\Project;

use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
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
                'description' => $this->project_type->description(),
            ],
            'client' => new CompanyResource($this->whenLoaded('client')),
            'manager' => new UserResource($this->whenLoaded('manager')),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'planned_budget' => $this->planned_budget,
            'actual_budget' => $this->actual_budget,
            'progress_percentage' => $this->progress_percentage,
            'address' => $this->address,
            'coordinates' => $this->coordinates,
            'metadata' => $this->metadata,
            
            // Computed fields
            'duration_days' => $this->getDurationInDays(),
            'budget_variance' => $this->getBudgetVariance(),
            'budget_variance_percentage' => $this->getBudgetVariancePercentage(),
            'time_elapsed_percentage' => $this->getTimeElapsedPercentage(),
            'is_overdue' => $this->isOverdue(),
            'is_over_budget' => $this->isOverBudget(),
            'can_be_edited' => $this->canBeEdited(),
            'can_be_deleted' => $this->canBeDeleted(),
            
            // Relationships counts when not loaded
            'phases_count' => $this->whenCounted('phases'),
            'tasks_count' => $this->whenCounted('tasks'),
            'milestones_count' => $this->whenCounted('milestones'),
            
            // Nested resources
            'phases' => ProjectPhaseResource::collection($this->whenLoaded('phases')),
            'tasks' => ProjectTaskResource::collection($this->whenLoaded('tasks')),
            'milestones' => ProjectMilestoneResource::collection($this->whenLoaded('milestones')),
            
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}