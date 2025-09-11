<?php

namespace App\Http\Controllers\Api\V1\Task;

use App\Domain\Task\Models\Task;
use App\Domain\Task\Models\TaskAttachment;
use App\Domain\Task\Services\TaskAttachmentService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Task\TaskAttachmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    public function __construct(
        protected TaskAttachmentService $attachmentService
    ) {}

    /**
     * Display a listing of task attachments
     */
    public function index(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $attachments = $task->attachments()
            ->with(['uploadedBy'])
            ->paginate(20);

        return response()->json([
            'data' => TaskAttachmentResource::collection($attachments->items()),
            'meta' => [
                'current_page' => $attachments->currentPage(),
                'last_page' => $attachments->lastPage(),
                'per_page' => $attachments->perPage(),
                'total' => $attachments->total(),
                'statistics' => $this->attachmentService->getTaskAttachmentStatistics($task)
            ]
        ]);
    }

    /**
     * Store multiple attachments for a task
     */
    public function store(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,webp,txt,csv,zip,rar'
        ]);

        try {
            $attachments = $this->attachmentService->uploadMultipleAttachments(
                $task,
                $request->file('files'),
                Auth::user()
            );

            return response()->json([
                'message' => 'Files uploaded successfully',
                'data' => TaskAttachmentResource::collection($attachments),
                'statistics' => $this->attachmentService->getTaskAttachmentStatistics($task)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload failed',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified attachment
     */
    public function show(TaskAttachment $attachment): JsonResponse
    {
        $this->authorize('view', $attachment->task);

        return response()->json([
            'data' => new TaskAttachmentResource($attachment->load(['task', 'uploadedBy']))
        ]);
    }

    /**
     * Download the specified attachment
     */
    public function download(TaskAttachment $attachment): Response
    {
        $this->authorize('view', $attachment->task);

        try {
            $content = $this->attachmentService->getAttachmentContent($attachment, Auth::user());
            
            return response($content, 200, [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'attachment; filename="' . $attachment->original_name . '"',
                'Content-Length' => $attachment->file_size,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'File not found or access denied'
            ], 404);
        }
    }

    /**
     * Generate and return thumbnail for image attachments
     */
    public function thumbnail(TaskAttachment $attachment): Response
    {
        $this->authorize('view', $attachment->task);

        if (!$attachment->isImage()) {
            return response()->json([
                'message' => 'Thumbnail not available for this file type'
            ], 400);
        }

        try {
            $thumbnailPath = $this->attachmentService->generateThumbnail($attachment);
            
            if (!$thumbnailPath || !file_exists($thumbnailPath)) {
                return response()->json(['message' => 'Thumbnail not found'], 404);
            }

            $content = file_get_contents($thumbnailPath);
            
            return response($content, 200, [
                'Content-Type' => $attachment->mime_type,
                'Cache-Control' => 'public, max-age=3600',
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Thumbnail generation failed'], 500);
        }
    }

    /**
     * Preview attachment in browser (for images and PDFs)
     */
    public function preview(TaskAttachment $attachment): Response
    {
        $this->authorize('view', $attachment->task);

        if (!$attachment->isImage() && $attachment->mime_type !== 'application/pdf') {
            return response()->json([
                'message' => 'Preview not available for this file type'
            ], 400);
        }

        try {
            $content = $this->attachmentService->getAttachmentContent($attachment, Auth::user());
            
            return response($content, 200, [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'inline; filename="' . $attachment->original_name . '"',
                'Cache-Control' => 'public, max-age=3600',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Preview not available'
            ], 404);
        }
    }

    /**
     * Remove the specified attachment
     */
    public function destroy(TaskAttachment $attachment): JsonResponse
    {
        $this->authorize('update', $attachment->task);

        try {
            $this->attachmentService->deleteAttachment($attachment, Auth::user());

            return response()->json([
                'message' => 'Attachment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Deletion failed',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get attachment upload configuration
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'max_file_size' => $this->attachmentService->getMaxFileSize(),
            'max_file_size_formatted' => number_format($this->attachmentService->getMaxFileSize() / 1024 / 1024, 1) . ' MB',
            'allowed_mime_types' => $this->attachmentService->getAllowedMimeTypes(),
            'max_files_per_upload' => 10
        ]);
    }

    /**
     * Bulk delete attachments
     */
    public function bulkDestroy(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $request->validate([
            'attachment_ids' => 'required|array',
            'attachment_ids.*' => 'required|uuid|exists:task_attachments,id'
        ]);

        $deletedCount = 0;
        $errors = [];

        foreach ($request->attachment_ids as $attachmentId) {
            try {
                $attachment = TaskAttachment::where('id', $attachmentId)
                    ->where('task_id', $task->id)
                    ->firstOrFail();

                $this->attachmentService->deleteAttachment($attachment, Auth::user());
                $deletedCount++;

            } catch (\Exception $e) {
                $errors[] = "Failed to delete attachment {$attachmentId}: " . $e->getMessage();
            }
        }

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} attachment(s)",
            'deleted_count' => $deletedCount,
            'errors' => $errors,
            'statistics' => $this->attachmentService->getTaskAttachmentStatistics($task)
        ]);
    }
}