<?php

namespace App\Domain\Task\Services;

use App\Domain\Task\Models\Task;
use App\Domain\Task\Models\TaskAttachment;
use App\Domain\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskAttachmentService
{
    protected array $allowedMimeTypes = [
        // Images
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'text/csv',
        // Archives
        'application/zip',
        'application/x-rar-compressed'
    ];

    protected int $maxFileSize = 10485760; // 10MB in bytes

    public function uploadAttachment(Task $task, UploadedFile $file, User $user): TaskAttachment
    {
        $this->validateFile($file);

        $fileName = $this->generateUniqueFileName($file);
        $filePath = $this->storeFile($file, $fileName);

        return TaskAttachment::create([
            'task_id' => $task->id,
            'uploaded_by_id' => $user->id,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $fileName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'metadata' => [
                'original_extension' => $file->getClientOriginalExtension(),
                'uploaded_at' => now()->toISOString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]
        ]);
    }

    public function uploadMultipleAttachments(Task $task, array $files, User $user): array
    {
        $attachments = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                try {
                    $attachments[] = $this->uploadAttachment($task, $file, $user);
                } catch (\Exception $e) {
                    // Log the error but continue with other files
                    \Log::error('Failed to upload attachment: ' . $e->getMessage(), [
                        'file' => $file->getClientOriginalName(),
                        'task_id' => $task->id,
                        'user_id' => $user->id
                    ]);
                }
            }
        }
        
        return $attachments;
    }

    public function deleteAttachment(TaskAttachment $attachment, User $user): bool
    {
        if (!$attachment->canBeDeletedBy($user)) {
            throw new \UnauthorizedHttpException('', 'You are not authorized to delete this attachment');
        }

        // Delete the physical file
        $this->deletePhysicalFile($attachment);

        // Delete the database record
        return $attachment->delete();
    }

    public function getAttachmentContent(TaskAttachment $attachment, User $user): string
    {
        if (!$attachment->canBeViewedBy($user)) {
            throw new \UnauthorizedHttpException('', 'You are not authorized to view this attachment');
        }

        $fullPath = storage_path('app/task-attachments/' . $attachment->file_path);
        
        if (!file_exists($fullPath)) {
            throw new \Exception('File not found on disk');
        }

        return file_get_contents($fullPath);
    }

    public function generateThumbnail(TaskAttachment $attachment, int $width = 150, int $height = 150): ?string
    {
        if (!$attachment->isImage()) {
            return null;
        }

        $fullPath = storage_path('app/task-attachments/' . $attachment->file_path);
        
        if (!file_exists($fullPath)) {
            return null;
        }

        // For now, return the original image path
        // In production, implement actual image resizing here
        return $fullPath;
    }

    public function getTaskAttachmentStatistics(Task $task): array
    {
        $attachments = $task->attachments;
        
        return [
            'total_count' => $attachments->count(),
            'total_size' => $attachments->sum('file_size'),
            'images_count' => $attachments->filter->isImage()->count(),
            'documents_count' => $attachments->filter->isDocument()->count(),
            'formatted_total_size' => $this->formatFileSize($attachments->sum('file_size')),
            'recent_uploads' => $attachments->where('created_at', '>=', now()->subDays(7))->count()
        ];
    }

    protected function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            throw new \InvalidArgumentException(
                'File size exceeds maximum allowed size of ' . $this->formatFileSize($this->maxFileSize)
            );
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \InvalidArgumentException(
                'File type ' . $file->getMimeType() . ' is not allowed'
            );
        }

        // Additional security checks
        $this->performSecurityChecks($file);
    }

    protected function performSecurityChecks(UploadedFile $file): void
    {
        // Check for executable file extensions
        $dangerousExtensions = ['php', 'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (in_array($extension, $dangerousExtensions)) {
            throw new \InvalidArgumentException('File extension is not allowed for security reasons');
        }

        // Check file content vs extension mismatch
        $realMimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file->getRealPath());
        if ($realMimeType !== $file->getMimeType()) {
            \Log::warning('MIME type mismatch detected', [
                'uploaded_mime' => $file->getMimeType(),
                'real_mime' => $realMimeType,
                'filename' => $file->getClientOriginalName()
            ]);
        }
    }

    protected function generateUniqueFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $uuid = Str::uuid();
        $timestamp = now()->format('Y/m/d');
        
        return $timestamp . '/' . $uuid . '.' . $extension;
    }

    protected function storeFile(UploadedFile $file, string $fileName): string
    {
        // Ensure directory exists
        $directory = dirname(storage_path('app/task-attachments/' . $fileName));
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Store the file
        $file->storeAs('task-attachments', $fileName);
        
        return $fileName;
    }

    protected function deletePhysicalFile(TaskAttachment $attachment): void
    {
        $fullPath = storage_path('app/task-attachments/' . $attachment->file_path);
        
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    public function setMaxFileSize(int $size): void
    {
        $this->maxFileSize = $size;
    }
}