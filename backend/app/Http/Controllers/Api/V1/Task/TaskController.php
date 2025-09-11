<?php

namespace App\Http\Controllers\Api\V1\Task;

use App\Domain\Task\DTOs\CreateTaskDTO;
use App\Domain\Task\DTOs\UpdateTaskDTO;
use App\Domain\Task\Enums\TaskStatus;
use App\Domain\Task\Services\TaskService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Task\TaskListResource;
use App\Http\Resources\Task\TaskResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}

    /**
     * Display a listing of tasks with filtering and pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min($request->integer('per_page', 50), 100);
        $sortBy = $request->string('sort_by', 'created_at');
        $sortDirection = $request->string('sort_direction', 'desc');
        
        $filters = $request->only([
            'status',
            'priority', 
            'task_type',
            'project_id',
            'phase_id',
            'assigned_to_id',
            'created_by_id',
            'parent_task_id',
            'start_date_from',
            'start_date_to',
            'due_date_from', 
            'due_date_to',
            'hours_min',
            'hours_max',
            'progress_min',
            'progress_max',
            'search',
            'overdue',
            'due_soon',
            'due_soon_days',
            'top_level_only',
            'subtasks_only'
        ]);

        // Use repository for advanced filtering
        $taskRepository = app(\App\Domain\Task\Repositories\Contracts\TaskRepositoryInterface::class);
        $tasks = $taskRepository->paginate(
            perPage: $perPage,
            filters: $filters,
            sortBy: $sortBy,
            sortDirection: $sortDirection,
            with: ['project', 'assignedTo', 'createdBy', 'phase', 'parentTask']
        );

        return TaskListResource::collection($tasks);
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:5000'],
                'project_id' => ['required', 'uuid', 'exists:projects,id'],
                'phase_id' => ['nullable', 'uuid', 'exists:project_phases,id'],
                'parent_task_id' => ['nullable', 'uuid', 'exists:tasks,id'],
                'task_type' => ['sometimes', 'string'],
                'priority' => ['sometimes', 'string'],
                'status' => ['sometimes', 'string'],
                'assigned_to_id' => ['nullable', 'uuid', 'exists:users,id'],
                'estimated_hours' => ['nullable', 'numeric', 'min:0'],
                'start_date' => ['nullable', 'date'],
                'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'task_order' => ['sometimes', 'integer', 'min:0'],
                'metadata' => ['sometimes', 'array'],
            ]);

            $validatedData['created_by_id'] = auth()->id();
            
            $taskDTO = CreateTaskDTO::fromArray($validatedData);
            $task = $this->taskService->createTask($taskDTO);

            return response()->json([
                'message' => 'Task created successfully',
                'data' => new TaskResource($task)
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified task.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $task = $this->taskService->getTask($id);
            
            return response()->json([
                'data' => new TaskResource($task)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
                'phase_id' => ['sometimes', 'nullable', 'uuid', 'exists:project_phases,id'],
                'parent_task_id' => ['sometimes', 'nullable', 'uuid', 'exists:tasks,id'],
                'task_type' => ['sometimes', 'string'],
                'priority' => ['sometimes', 'string'],
                'status' => ['sometimes', 'string'],
                'assigned_to_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
                'estimated_hours' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                'actual_hours' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                'progress_percentage' => ['sometimes', 'integer', 'between:0,100'],
                'start_date' => ['sometimes', 'nullable', 'date'],
                'due_date' => ['sometimes', 'nullable', 'date'],
                'task_order' => ['sometimes', 'integer', 'min:0'],
                'metadata' => ['sometimes', 'nullable', 'array'],
            ]);
            
            $updateDTO = UpdateTaskDTO::fromArray($validatedData);
            $task = $this->taskService->updateTask($id, $updateDTO);

            return response()->json([
                'message' => 'Task updated successfully',
                'data' => new TaskResource($task)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified task.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->taskService->deleteTask($id);

            return response()->json([
                'message' => 'Task deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update task status.
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:not_started,in_progress,review,completed,cancelled,on_hold']
        ]);

        try {
            $status = TaskStatus::from($request->status);
            $task = $this->taskService->updateTaskStatus($id, $status);

            return response()->json([
                'message' => 'Task status updated successfully',
                'data' => new TaskResource($task)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update task status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update task progress.
     */
    public function updateProgress(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'progress_percentage' => ['required', 'integer', 'between:0,100']
        ]);

        try {
            $task = $this->taskService->updateProgress($id, $request->progress_percentage);

            return response()->json([
                'message' => 'Task progress updated successfully',
                'data' => [
                    'progress_percentage' => $task->progress_percentage,
                    'status' => $task->status->value
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Assign task to user.
     */
    public function assign(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'assigned_to_id' => ['nullable', 'uuid', 'exists:users,id']
        ]);

        try {
            $task = $this->taskService->assignTask($id, $request->assigned_to_id);

            return response()->json([
                'message' => 'Task assigned successfully',
                'data' => new TaskResource($task)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }
    }

    /**
     * Log time for task.
     */
    public function logTime(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'hours' => ['required', 'numeric', 'min:0.1', 'max:24']
        ]);

        try {
            $task = $this->taskService->logTime($id, $request->hours);

            return response()->json([
                'message' => 'Time logged successfully',
                'data' => [
                    'actual_hours' => $task->actual_hours,
                    'estimated_hours' => $task->estimated_hours,
                    'time_variance' => $task->getTimeVariance()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get tasks by project.
     */
    public function byProject(string $projectId): AnonymousResourceCollection
    {
        $tasks = $this->taskService->getProjectTasks($projectId);
        return TaskListResource::collection($tasks);
    }

    /**
     * Get tasks by assignee.
     */
    public function byAssignee(string $userId): AnonymousResourceCollection
    {
        $tasks = $this->taskService->getUserTasks($userId);
        return TaskListResource::collection($tasks);
    }

    /**
     * Search tasks.
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'query' => ['required', 'string', 'min:2']
        ]);

        $tasks = $this->taskService->searchTasks($request->input('query'));
        return TaskListResource::collection($tasks);
    }

    /**
     * Get overdue tasks.
     */
    public function overdue(): AnonymousResourceCollection
    {
        $tasks = $this->taskService->getOverdueTasks();
        return TaskListResource::collection($tasks);
    }

    /**
     * Get tasks due soon.
     */
    public function dueSoon(Request $request): AnonymousResourceCollection
    {
        $days = $request->integer('days', 7);
        $tasks = $this->taskService->getTasksDueSoon($days);
        return TaskListResource::collection($tasks);
    }

    /**
     * Get task statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $projectId = $request->string('project_id');
        $statistics = $this->taskService->getTaskStatistics($projectId);
        
        return response()->json([
            'data' => $statistics
        ]);
    }

    /**
     * Get task hierarchy.
     */
    public function hierarchy(string $id): JsonResponse
    {
        try {
            $hierarchy = $this->taskService->getTaskHierarchy($id);
            
            return response()->json([
                'data' => TaskListResource::collection($hierarchy)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }
    }

    /**
     * Duplicate task.
     */
    public function duplicate(Request $request, string $id): JsonResponse
    {
        try {
            $overrides = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'assigned_to_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
                'due_date' => ['sometimes', 'nullable', 'date'],
            ]);

            $task = $this->taskService->duplicateTask($id, $overrides);

            return response()->json([
                'message' => 'Task duplicated successfully',
                'data' => new TaskResource($task)
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }
    }

    /**
     * Bulk update tasks.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'task_ids' => ['required', 'array', 'max:50'],
            'task_ids.*' => ['uuid', 'exists:tasks,id'],
            'updates' => ['required', 'array'],
            'updates.status' => ['sometimes', 'string'],
            'updates.priority' => ['sometimes', 'string'],
            'updates.assigned_to_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
        ]);

        try {
            $updatedCount = $this->taskService->bulkUpdateTasks(
                $request->task_ids,
                $request->updates
            );

            return response()->json([
                'message' => "Updated {$updatedCount} tasks successfully",
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update tasks',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}