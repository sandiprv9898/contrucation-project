<?php

namespace App\Http\Resources\Gantt;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class GanttTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type ?? 'task',
            'status' => $this->status,
            'start_date' => $this->start_date ? Carbon::parse($this->start_date)->toDateString() : null,
            'end_date' => $this->end_date ? Carbon::parse($this->end_date)->toDateString() : null,
            'startDate' => $this->start_date ? Carbon::parse($this->start_date)->toDateString() : null,
            'endDate' => $this->end_date ? Carbon::parse($this->end_date)->toDateString() : null,
            'duration' => $this->calculateDuration(),
            'progress' => $this->progress ?? 0,
            'priority' => $this->priority ?? 'medium',
            'assignees' => $this->assignees ?? [],
            'parent_id' => $this->parent_id,
            'parentId' => $this->parent_id,
            'level' => $this->level ?? 0,
            'critical' => $this->isCritical(),
            'color' => $this->getTaskColor(),
            'project_id' => $this->project_id,
            'projectId' => $this->project_id,
            'phase_id' => $this->phase_id,
            'phaseId' => $this->phase_id,
            'estimated_hours' => $this->estimated_hours ?? 0,
            'estimatedHours' => $this->estimated_hours ?? 0,
            'actual_hours' => $this->actual_hours ?? 0,
            'actualHours' => $this->actual_hours ?? 0,
            'cost' => $this->cost ?? 0,
            'tags' => $this->tags ?? [],
            'created_at' => $this->created_at?->toISOString(),
            'createdAt' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'children' => $this->when(
                $this->relationLoaded('children'),
                fn() => GanttTaskResource::collection($this->children)
            ),
            'dependencies' => $this->when(
                $this->relationLoaded('dependencies'),
                fn() => $this->dependencies->pluck('depends_on_id')
            ),
        ];
    }

    private function calculateDuration(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);
        
        return $start->diffInDays($end) + 1;
    }

    private function isCritical(): bool
    {
        return $this->priority === 'critical' || $this->critical === true;
    }

    private function getTaskColor(): string
    {
        return match($this->status) {
            'completed' => '#10b981', // green
            'in_progress' => '#3b82f6', // blue
            'on_hold' => '#f59e0b', // amber
            'cancelled' => '#ef4444', // red
            default => '#6b7280', // gray
        };
    }
}