<?php

namespace App\Http\Resources\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectPhaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'phase_order' => $this->phase_order,
            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'estimated_duration_days' => $this->estimated_duration_days,
            'actual_duration_days' => $this->actual_duration_days,
            'budget_allocation' => $this->budget_allocation,
            'actual_cost' => $this->actual_cost,
            'progress_percentage' => $this->getProgressPercentage(),
            'is_overdue' => $this->isOverdue(),
            'is_over_budget' => $this->isOverBudget(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}