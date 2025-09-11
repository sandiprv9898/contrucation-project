<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskDependencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dependency_type' => [
                'value' => $this->dependency_type->value,
                'label' => $this->dependency_type->label(),
                'description' => $this->dependency_type->description(),
            ],
            'lag_days' => $this->lag_days,
            'task' => $this->whenLoaded('task', function () {
                return [
                    'id' => $this->task->id,
                    'name' => $this->task->name,
                    'status' => $this->task->status->value,
                ];
            }),
            'prerequisite_task' => $this->whenLoaded('prerequisiteTask', function () {
                return [
                    'id' => $this->prerequisiteTask->id,
                    'name' => $this->prerequisiteTask->name,
                    'status' => $this->prerequisiteTask->status->value,
                ];
            }),
            'is_blocking' => $this->isBlocking(),
            'earliest_start_date' => $this->getEarliestStartDate()?->format('Y-m-d'),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}