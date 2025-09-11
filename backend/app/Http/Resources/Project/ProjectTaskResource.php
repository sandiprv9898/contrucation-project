<?php

namespace App\Http\Resources\Project;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'assigned_to' => new UserResource($this->whenLoaded('assignedTo')),
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'completed_at' => $this->completed_at?->toISOString(),
            'dependencies' => $this->dependencies,
            'is_overdue' => $this->isOverdue(),
            'is_completed' => $this->isCompleted(),
            'time_spent_percentage' => $this->getTimeSpentPercentage(),
            'can_be_started' => $this->canBeStarted(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}