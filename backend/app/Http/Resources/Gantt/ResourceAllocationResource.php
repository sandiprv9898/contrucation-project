<?php

namespace App\Http\Resources\Gantt;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceAllocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'] ?? null,
            'name' => $this->resource['name'] ?? 'Unknown User',
            'type' => $this->resource['type'] ?? 'person',
            'tasks' => $this->when(
                isset($this->resource['tasks']),
                fn() => GanttTaskResource::collection($this->resource['tasks'])
            ),
            'total_hours' => $this->resource['total_hours'] ?? 0,
            'totalHours' => $this->resource['total_hours'] ?? 0,
            'allocation_percentage' => $this->resource['allocation_percentage'] ?? 0,
            'allocationPercentage' => $this->resource['allocation_percentage'] ?? 0,
            'cost' => $this->resource['cost'] ?? 0,
            'availability' => $this->resource['availability'] ?? null,
            'overallocated' => ($this->resource['allocation_percentage'] ?? 0) > 100,
        ];
    }
}