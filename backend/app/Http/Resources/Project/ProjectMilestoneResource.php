<?php

namespace App\Http\Resources\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMilestoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'target_date' => $this->target_date?->format('Y-m-d'),
            'completed_date' => $this->completed_date?->format('Y-m-d'),
            'status' => $this->status,
            'milestone_type' => $this->milestone_type,
            'is_overdue' => $this->isOverdue(),
            'is_completed' => $this->isCompleted(),
            'is_upcoming' => $this->isUpcoming(),
            'days_until_target' => $this->getDaysUntilTarget(),
            'days_overdue' => $this->getDaysOverdue(),
            'status_color' => $this->getStatusColor(),
            'status_label' => $this->getStatusLabel(),
            'type_label' => $this->getTypeLabel(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}