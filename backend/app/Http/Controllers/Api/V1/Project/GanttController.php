<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Domain\Project\Services\GanttService;
use App\Domain\Task\Services\TaskDependencyService;
use App\Http\Requests\Gantt\GanttFilterRequest;
use App\Http\Requests\Gantt\AutoScheduleRequest;
use App\Http\Requests\Gantt\ExportGanttRequest;
use App\Http\Requests\Gantt\ImportGanttRequest;
use App\Http\Resources\Gantt\GanttTaskResource;
use App\Http\Resources\Gantt\TaskDependencyResource;
use App\Http\Resources\Gantt\CriticalPathResource;
use App\Http\Resources\Gantt\ResourceAllocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class GanttController extends Controller
{
    public function __construct(
        private GanttService $ganttService,
        private TaskDependencyService $dependencyService
    ) {}

    /**
     * Get Gantt chart data for a project
     */
    public function index(string $projectId, GanttFilterRequest $request): JsonResponse
    {
        $filters = $request->validated();
        
        $ganttData = $this->ganttService->getProjectGanttData($projectId, $filters);
        
        return response()->json([
            'tasks' => GanttTaskResource::collection($ganttData['tasks']),
            'dependencies' => TaskDependencyResource::collection($ganttData['dependencies']),
            'statistics' => $ganttData['statistics'],
            'timeline' => $ganttData['timeline']
        ]);
    }

    /**
     * Get all tasks in Gantt format
     */
    public function getTasks(string $projectId, Request $request): JsonResponse
    {
        $view = $request->only([
            'scale', 'startDate', 'endDate', 'showWeekends', 
            'showDependencies', 'showCriticalPath', 'showProgress',
            'showResources', 'showMilestones', 'groupBy'
        ]);
        
        $tasks = $this->ganttService->getGanttTasks($projectId, $view);
        
        return response()->json([
            'tasks' => GanttTaskResource::collection($tasks),
            'meta' => [
                'total' => $tasks->count(),
                'timeline' => $this->ganttService->calculateTimeline($tasks)
            ]
        ]);
    }

    /**
     * Get task dependencies for a project
     */
    public function getDependencies(string $projectId): JsonResponse
    {
        $dependencies = $this->dependencyService->getProjectDependencies($projectId);
        
        return response()->json([
            'dependencies' => TaskDependencyResource::collection($dependencies),
            'meta' => [
                'total' => $dependencies->count(),
                'has_circular' => $this->dependencyService->hasCircularDependencies($projectId)
            ]
        ]);
    }

    /**
     * Get critical path for a project
     */
    public function getCriticalPath(string $projectId): JsonResponse
    {
        $criticalPath = $this->ganttService->calculateCriticalPath($projectId);
        
        return response()->json(new CriticalPathResource($criticalPath));
    }

    /**
     * Auto-schedule tasks based on dependencies and constraints
     */
    public function autoSchedule(string $projectId, AutoScheduleRequest $request): JsonResponse
    {
        $options = $request->validated();
        
        DB::beginTransaction();
        try {
            $result = $this->ganttService->autoScheduleTasks($projectId, $options);
            
            DB::commit();
            
            return response()->json([
                'tasks' => GanttTaskResource::collection($result['tasks']),
                'changes' => $result['changes'],
                'conflicts' => $result['conflicts'],
                'message' => 'Tasks have been auto-scheduled successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to auto-schedule tasks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Gantt chart in various formats
     */
    public function export(string $projectId, ExportGanttRequest $request)
    {
        $options = $request->validated();
        
        $export = $this->ganttService->exportGantt($projectId, $options);
        
        return response()->download(
            $export['path'],
            $export['filename'],
            ['Content-Type' => $export['mime']]
        )->deleteFileAfterSend(true);
    }

    /**
     * Import Gantt data from external files
     */
    public function import(string $projectId, ImportGanttRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $format = $request->input('format');
        
        DB::beginTransaction();
        try {
            $result = $this->ganttService->importGantt($projectId, $file, $format);
            
            DB::commit();
            
            return response()->json([
                'tasks' => GanttTaskResource::collection($result['tasks']),
                'dependencies' => TaskDependencyResource::collection($result['dependencies']),
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
                'errors' => $result['errors'],
                'message' => "Successfully imported {$result['imported']} tasks"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to import Gantt data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get resource allocation for the project
     */
    public function getResourceAllocation(string $projectId, Request $request): JsonResponse
    {
        $dateRange = $request->only(['start', 'end']);
        
        $allocation = $this->ganttService->getResourceAllocation($projectId, $dateRange);
        
        return response()->json([
            'resources' => ResourceAllocationResource::collection($allocation['resources']),
            'timeline' => $allocation['timeline'],
            'statistics' => $allocation['statistics']
        ]);
    }

    /**
     * Validate task dependencies for circular references
     */
    public function validateDependencies(string $projectId): JsonResponse
    {
        $validation = $this->dependencyService->validateProjectDependencies($projectId);
        
        return response()->json([
            'valid' => $validation['valid'],
            'circular_dependencies' => $validation['circular'],
            'orphaned_dependencies' => $validation['orphaned'],
            'conflicts' => $validation['conflicts'],
            'warnings' => $validation['warnings']
        ]);
    }

    /**
     * Get Gantt chart statistics
     */
    public function getStatistics(string $projectId): JsonResponse
    {
        $stats = $this->ganttService->getGanttStatistics($projectId);
        
        return response()->json($stats);
    }

    /**
     * Update multiple tasks in bulk
     */
    public function bulkUpdateTasks(Request $request): JsonResponse
    {
        $updates = $request->input('updates', []);
        
        DB::beginTransaction();
        try {
            $results = $this->ganttService->bulkUpdateTasks($updates);
            
            DB::commit();
            
            return response()->json([
                'updated' => $results['updated'],
                'failed' => $results['failed'],
                'tasks' => GanttTaskResource::collection($results['tasks']),
                'message' => "Successfully updated {$results['updated']} tasks"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to update tasks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize project schedule
     */
    public function optimizeSchedule(string $projectId, Request $request): JsonResponse
    {
        $criteria = $request->input('criteria', 'duration'); // duration, cost, resources
        
        DB::beginTransaction();
        try {
            $result = $this->ganttService->optimizeSchedule($projectId, $criteria);
            
            DB::commit();
            
            return response()->json([
                'tasks' => GanttTaskResource::collection($result['tasks']),
                'improvements' => $result['improvements'],
                'original_duration' => $result['original_duration'],
                'optimized_duration' => $result['optimized_duration'],
                'savings' => $result['savings'],
                'message' => 'Schedule has been optimized successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to optimize schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}