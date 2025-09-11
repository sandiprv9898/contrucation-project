<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskAttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'original_name' => $this->original_name,
            'file_size' => $this->file_size,
            'formatted_file_size' => $this->getFormattedFileSize(),
            'mime_type' => $this->mime_type,
            'file_extension' => $this->getFileExtension(),
            'is_image' => $this->isImage(),
            'is_document' => $this->isDocument(),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'uploaded_by' => $this->whenLoaded('uploadedBy', function () {
                return [
                    'id' => $this->uploadedBy->id,
                    'name' => $this->uploadedBy->name,
                    'avatar_url' => $this->uploadedBy->avatar_url ?? null,
                ];
            }),
            
            'task' => $this->whenLoaded('task', function () {
                return [
                    'id' => $this->task->id,
                    'name' => $this->task->name,
                ];
            }),
            
            // URLs
            'download_url' => $this->getDownloadUrl(),
            'preview_url' => $this->getPreviewUrl(),
            'thumbnail_url' => $this->getThumbnailUrl(),
            
            // Permissions
            'can_delete' => $this->canBeDeletedBy($request->user()),
            'can_view' => $this->canBeViewedBy($request->user()),
        ];
    }
}