<?php

namespace App\Http\Resources\Task;

use App\Http\Resources\Project\ProjectListResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
                'can_transition' => $this->status->getOptions(), // Available transitions
            ],
            'priority' => [
                'value' => $this->priority->value,
                'label' => $this->priority->label(),
                'color' => $this->priority->color(),
                'weight' => $this->priority->weight(),
            ],
            'task_type' => [
                'value' => $this->task_type->value,
                'label' => $this->task_type->label(),
                'color' => $this->task_type->color(),
                'category' => $this->task_type->category(),
                'required_skills' => $this->task_type->requiresSkills(),
            ],
            'project' => new ProjectListResource($this->whenLoaded('project')),
            'phase' => $this->whenLoaded('phase', function () {
                return [
                    'id' => $this->phase->id,
                    'name' => $this->phase->name,
                    'status' => $this->phase->status,
                ];
            }),
            'parent_task' => $this->whenLoaded('parentTask', function () {
                return [
                    'id' => $this->parentTask->id,
                    'name' => $this->parentTask->name,
                    'status' => $this->parentTask->status->value,
                ];
            }),
            'sub_tasks' => TaskListResource::collection($this->whenLoaded('subTasks')),
            'assigned_to' => new UserResource($this->whenLoaded('assignedTo')),
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'progress_percentage' => $this->progress_percentage,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'completed_at' => $this->completed_at?->toISOString(),
            'task_order' => $this->task_order,
            'metadata' => $this->metadata,
            
            // Computed fields
            'duration_days' => $this->getDurationInDays(),
            'time_variance' => $this->getTimeVariance(),
            'time_variance_percentage' => $this->getTimeVariancePercentage(),
            'is_overdue' => $this->isOverdue(),
            'is_due_soon' => $this->isDueSoon(),
            'is_top_level' => $this->isTopLevel(),
            'level' => $this->getLevel(),
            'can_be_started' => $this->canBeStarted(),
            'can_be_edited' => $this->canBeEdited(),
            'can_be_deleted' => $this->canBeDeleted(),
            'has_blocking_dependencies' => $this->hasBlockingDependencies(),
            
            // Dependencies
            'dependencies' => TaskDependencyResource::collection($this->whenLoaded('dependencies')),
            'dependent_tasks' => TaskDependencyResource::collection($this->whenLoaded('dependentTasks')),
            
            // Comments
            'comments' => TaskCommentResource::collection($this->whenLoaded('comments')),
            'comments_count' => $this->whenCounted('comments'),
            
            // Timestamps
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}