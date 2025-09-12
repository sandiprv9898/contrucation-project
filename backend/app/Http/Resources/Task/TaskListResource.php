<?php

namespace App\Http\Resources\Task;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description ? substr($this->description, 0, 100) . '...' : null,
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
            'task_type' => [
                'value' => $this->task_type->value,
                'label' => $this->task_type->label(),
                'color' => $this->task_type->color(),
                'category' => $this->task_type->category(),
            ],
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                    'status' => $this->project->status->value,
                ];
            }),
            'phase' => $this->whenLoaded('phase', function () {
                return [
                    'id' => $this->phase->id,
                    'name' => $this->phase->name,
                ];
            }),
            'parent_task' => $this->whenLoaded('parentTask', function () {
                return [
                    'id' => $this->parentTask->id,
                    'name' => $this->parentTask->name,
                ];
            }),
            'assigned_to' => new UserResource($this->whenLoaded('assignedTo')),
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'progress_percentage' => $this->progress_percentage,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'completed_at' => $this->completed_at?->toISOString(),
            'task_order' => $this->task_order,
            
            // Computed fields
            'is_overdue' => $this->isOverdue(),
            'is_due_soon' => $this->isDueSoon(),
            'is_top_level' => $this->isTopLevel(),
            'level' => $this->getLevel(),
            'has_active_timer' => (bool) $this->getAttribute('has_active_timer'),
            'sub_tasks_count' => $this->whenCounted('subTasks'),
            'comments_count' => $this->whenCounted('comments'),
            'dependencies_count' => $this->whenCounted('dependencies'),
            
            // Timestamps
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}