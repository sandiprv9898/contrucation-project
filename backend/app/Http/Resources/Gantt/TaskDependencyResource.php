<?php

namespace App\Http\Resources\Gantt;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskDependencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'taskId' => $this->task_id,
            'depends_on_id' => $this->depends_on_id,
            'dependsOnId' => $this->depends_on_id,
            'type' => $this->type ?? 'finish_to_start',
            'lag' => $this->lag ?? 0,
            'critical' => $this->critical ?? false,
            'name' => $this->name ?? '',
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Related task information
            'task_name' => $this->whenLoaded('task', fn() => $this->task->name),
            'depends_on_task_name' => $this->whenLoaded('dependsOnTask', fn() => $this->dependsOnTask->name),
        ];
    }
}