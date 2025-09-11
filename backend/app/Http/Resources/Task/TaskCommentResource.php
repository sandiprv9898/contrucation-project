<?php

namespace App\Http\Resources\Task;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'is_internal' => $this->is_internal,
            'user' => new UserResource($this->whenLoaded('user')),
            'task' => $this->whenLoaded('task', function () {
                return [
                    'id' => $this->task->id,
                    'name' => $this->task->name,
                ];
            }),
            'time_ago' => $this->getTimeAgo(),
            'is_recent' => $this->isRecent(),
            'can_edit' => $this->canBeEditedBy(auth()->user()),
            'can_delete' => $this->canBeDeletedBy(auth()->user()),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}