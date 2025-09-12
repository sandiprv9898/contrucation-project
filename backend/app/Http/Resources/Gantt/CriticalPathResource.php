<?php

namespace App\Http\Resources\Gantt;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CriticalPathResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'tasks' => $this->resource['tasks'] ?? [],
            'total_duration' => $this->resource['totalDuration'] ?? 0,
            'totalDuration' => $this->resource['totalDuration'] ?? 0,
            'start_date' => $this->resource['startDate'] ?? null,
            'startDate' => $this->resource['startDate'] ?? null,
            'end_date' => $this->resource['endDate'] ?? null,
            'endDate' => $this->resource['endDate'] ?? null,
            'slack' => $this->resource['slack'] ?? 0,
            'task_count' => count($this->resource['tasks'] ?? []),
            'taskCount' => count($this->resource['tasks'] ?? []),
        ];
    }
}