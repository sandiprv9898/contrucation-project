<?php

namespace App\Domain\Task\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAttachment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'task_id',
        'uploaded_by_id',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
        'metadata',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    // Scopes
    public function scopeByTask($query, string $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('uploaded_by_id', $userId);
    }

    public function scopeByMimeType($query, string $mimeType)
    {
        return $query->where('mime_type', $mimeType);
    }

    public function scopeImages($query)
    {
        return $query->where('mime_type', 'LIKE', 'image/%');
    }

    public function scopeDocuments($query)
    {
        return $query->whereIn('mime_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }

    // Business Logic Methods
    public function getFileExtension(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isDocument(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain'
        ]);
    }

    public function canBeDeletedBy(User $user): bool
    {
        return $this->uploaded_by_id === $user->id || 
               $user->hasRole(['admin', 'project_manager']) ||
               $this->task->canBeEditedBy($user);
    }

    public function canBeViewedBy(User $user): bool
    {
        return $this->task->canBeViewedBy($user);
    }

    public function getDownloadUrl(): string
    {
        return route('api.v1.attachments.download', $this->id);
    }

    public function getThumbnailUrl(): ?string
    {
        if ($this->isImage()) {
            return route('api.v1.attachments.thumbnail', $this->id);
        }
        
        return null;
    }

    public function getPreviewUrl(): ?string
    {
        if ($this->mime_type === 'application/pdf' || $this->isImage()) {
            return route('api.v1.attachments.preview', $this->id);
        }
        
        return null;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            // Delete physical file when model is deleted
            if (file_exists(storage_path('app/task-attachments/' . $attachment->file_path))) {
                unlink(storage_path('app/task-attachments/' . $attachment->file_path));
            }
        });
    }
}