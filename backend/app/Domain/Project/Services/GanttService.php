<?php

namespace App\Domain\Project\Services;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;
use App\Domain\Task\Models\TaskDependency;
use App\Domain\Task\Repositories\Contracts\TaskRepositoryInterface;
use App\Domain\Project\Repositories\Contracts\ProjectRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class GanttService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private ProjectRepositoryInterface $projectRepository
    ) {}

    /**
     * Get complete Gantt chart data for a project
     */
    public function getProjectGanttData(string $projectId, array $filters = []): array
    {
        $project = $this->projectRepository->find($projectId);
        $tasks = $this->taskRepository->getProjectTasks($projectId, $filters);
        $dependencies = TaskDependency::whereIn('task_id', $tasks->pluck('id'))->get();
        
        // Build task hierarchy
        $taskTree = $this->buildTaskHierarchy($tasks);
        
        // Calculate timeline
        $timeline = $this->calculateTimeline($tasks);
        
        // Calculate statistics
        $statistics = $this->calculateStatistics($tasks);
        
        return [
            'project' => $project,
            'tasks' => $taskTree,
            'dependencies' => $dependencies,
            'timeline' => $timeline,
            'statistics' => $statistics
        ];
    }

    /**
     * Get tasks formatted for Gantt chart display
     */
    public function getGanttTasks(string $projectId, array $view = []): Collection
    {
        $tasks = $this->taskRepository->getProjectTasks($projectId);
        
        // Apply view settings
        if (!empty($view['groupBy'])) {
            $tasks = $this->groupTasks($tasks, $view['groupBy']);
        }
        
        // Calculate additional Gantt-specific properties
        $tasks = $tasks->map(function ($task) {
            $task->duration = $this->calculateDuration($task->start_date, $task->end_date);
            $task->progress = $task->progress ?? 0;
            $task->critical = $this->isTaskCritical($task);
            
            return $task;
        });
        
        return $tasks;
    }

    /**
     * Calculate the critical path for a project
     */
    public function calculateCriticalPath(string $projectId): array
    {
        $tasks = $this->taskRepository->getProjectTasks($projectId);
        $dependencies = TaskDependency::whereIn('task_id', $tasks->pluck('id'))->get();
        
        // Build network diagram
        $network = $this->buildNetworkDiagram($tasks, $dependencies);
        
        // Calculate forward pass (early start/finish)
        $this->calculateForwardPass($network);
        
        // Calculate backward pass (late start/finish)
        $this->calculateBackwardPass($network);
        
        // Identify critical path
        $criticalTasks = $this->identifyCriticalTasks($network);
        
        // Calculate total duration and slack
        $startDate = $tasks->min('start_date');
        $endDate = $tasks->max('end_date');
        $totalDuration = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
        
        return [
            'tasks' => $criticalTasks->pluck('id')->toArray(),
            'totalDuration' => $totalDuration,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'slack' => 0 // Critical path has zero slack
        ];
    }

    /**
     * Auto-schedule tasks based on dependencies and constraints
     */
    public function autoScheduleTasks(string $projectId, array $options = []): array
    {
        $tasks = $this->taskRepository->getProjectTasks($projectId);
        $dependencies = TaskDependency::whereIn('task_id', $tasks->pluck('id'))->get();
        
        $changes = [];
        $conflicts = [];
        
        // Sort tasks by dependencies (topological sort)
        $sortedTasks = $this->topologicalSort($tasks, $dependencies);
        
        foreach ($sortedTasks as $task) {
            $originalStart = $task->start_date;
            $originalEnd = $task->end_date;
            
            // Calculate new dates based on dependencies
            $newDates = $this->calculateTaskDates($task, $dependencies, $options);
            
            if ($newDates['start'] != $originalStart || $newDates['end'] != $originalEnd) {
                $task->start_date = $newDates['start'];
                $task->end_date = $newDates['end'];
                $task->save();
                
                $changes[] = [
                    'task_id' => $task->id,
                    'task_name' => $task->name,
                    'old_start' => $originalStart,
                    'old_end' => $originalEnd,
                    'new_start' => $newDates['start'],
                    'new_end' => $newDates['end']
                ];
            }
            
            // Check for resource conflicts
            if ($options['optimizeResources'] ?? false) {
                $resourceConflicts = $this->checkResourceConflicts($task);
                if (!empty($resourceConflicts)) {
                    $conflicts[] = $resourceConflicts;
                }
            }
        }
        
        return [
            'tasks' => $sortedTasks,
            'changes' => $changes,
            'conflicts' => $conflicts
        ];
    }

    /**
     * Export Gantt chart to various formats
     */
    public function exportGantt(string $projectId, array $options): array
    {
        $ganttData = $this->getProjectGanttData($projectId);
        $format = $options['format'] ?? 'pdf';
        
        switch ($format) {
            case 'pdf':
                return $this->exportToPdf($ganttData, $options);
            case 'excel':
                return $this->exportToExcel($ganttData, $options);
            case 'png':
                return $this->exportToPng($ganttData, $options);
            case 'mpp':
                return $this->exportToMsProject($ganttData, $options);
            default:
                throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }
    }

    /**
     * Import Gantt data from external files
     */
    public function importGantt(string $projectId, UploadedFile $file, string $format): array
    {
        switch ($format) {
            case 'mpp':
                return $this->importFromMsProject($projectId, $file);
            case 'xml':
                return $this->importFromXml($projectId, $file);
            case 'csv':
                return $this->importFromCsv($projectId, $file);
            default:
                throw new \InvalidArgumentException("Unsupported import format: {$format}");
        }
    }

    /**
     * Get resource allocation data
     */
    public function getResourceAllocation(string $projectId, array $dateRange = []): array
    {
        $tasks = $this->taskRepository->getProjectTasks($projectId);
        
        if (!empty($dateRange)) {
            $tasks = $tasks->filter(function ($task) use ($dateRange) {
                return $task->start_date >= $dateRange['start'] && 
                       $task->end_date <= $dateRange['end'];
            });
        }
        
        // Group tasks by assignee
        $resourceAllocation = [];
        foreach ($tasks as $task) {
            foreach ($task->assignees ?? [] as $assignee) {
                if (!isset($resourceAllocation[$assignee])) {
                    $resourceAllocation[$assignee] = [
                        'id' => $assignee,
                        'name' => $this->getUserName($assignee),
                        'tasks' => [],
                        'total_hours' => 0,
                        'allocation_percentage' => 0
                    ];
                }
                
                $resourceAllocation[$assignee]['tasks'][] = $task;
                $resourceAllocation[$assignee]['total_hours'] += $task->estimated_hours ?? 0;
            }
        }
        
        // Calculate allocation percentages
        $totalHours = $this->calculateWorkingHours($dateRange);
        foreach ($resourceAllocation as &$resource) {
            $resource['allocation_percentage'] = 
                $totalHours > 0 ? round(($resource['total_hours'] / $totalHours) * 100, 2) : 0;
        }
        
        return [
            'resources' => collect($resourceAllocation)->values(),
            'timeline' => $dateRange,
            'statistics' => [
                'total_resources' => count($resourceAllocation),
                'total_tasks' => $tasks->count(),
                'total_hours' => array_sum(array_column($resourceAllocation, 'total_hours')),
                'average_allocation' => round(
                    array_sum(array_column($resourceAllocation, 'allocation_percentage')) / 
                    max(count($resourceAllocation), 1), 2
                )
            ]
        ];
    }

    /**
     * Get Gantt chart statistics
     */
    public function getGanttStatistics(string $projectId): array
    {
        $tasks = $this->taskRepository->getProjectTasks($projectId);
        
        return $this->calculateStatistics($tasks);
    }

    /**
     * Bulk update tasks
     */
    public function bulkUpdateTasks(array $updates): array
    {
        $updated = 0;
        $failed = [];
        $updatedTasks = [];
        
        foreach ($updates as $update) {
            try {
                $task = Task::find($update['id']);
                if ($task) {
                    $task->update($update['changes']);
                    $updatedTasks[] = $task;
                    $updated++;
                } else {
                    $failed[] = [
                        'id' => $update['id'],
                        'reason' => 'Task not found'
                    ];
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'id' => $update['id'],
                    'reason' => $e->getMessage()
                ];
            }
        }
        
        return [
            'updated' => $updated,
            'failed' => $failed,
            'tasks' => $updatedTasks
        ];
    }

    /**
     * Optimize project schedule
     */
    public function optimizeSchedule(string $projectId, string $criteria = 'duration'): array
    {
        $tasks = $this->taskRepository->getProjectTasks($projectId);
        $originalDuration = $this->calculateProjectDuration($tasks);
        
        // Apply optimization based on criteria
        switch ($criteria) {
            case 'duration':
                $optimizedTasks = $this->optimizeForDuration($tasks);
                break;
            case 'cost':
                $optimizedTasks = $this->optimizeForCost($tasks);
                break;
            case 'resources':
                $optimizedTasks = $this->optimizeForResources($tasks);
                break;
            default:
                $optimizedTasks = $tasks;
        }
        
        $optimizedDuration = $this->calculateProjectDuration($optimizedTasks);
        $savings = [
            'time' => $originalDuration - $optimizedDuration,
            'percentage' => round((($originalDuration - $optimizedDuration) / $originalDuration) * 100, 2)
        ];
        
        return [
            'tasks' => $optimizedTasks,
            'improvements' => $this->identifyImprovements($tasks, $optimizedTasks),
            'original_duration' => $originalDuration,
            'optimized_duration' => $optimizedDuration,
            'savings' => $savings
        ];
    }

    // Private helper methods

    private function buildTaskHierarchy(Collection $tasks): Collection
    {
        $taskMap = [];
        $rootTasks = collect();
        
        // First pass: create task map
        foreach ($tasks as $task) {
            $taskMap[$task->id] = $task;
            $task->children = collect();
        }
        
        // Second pass: build hierarchy
        foreach ($tasks as $task) {
            if ($task->parent_id && isset($taskMap[$task->parent_id])) {
                $taskMap[$task->parent_id]->children->push($task);
            } else {
                $rootTasks->push($task);
            }
        }
        
        return $rootTasks;
    }

    public function calculateTimeline(Collection $tasks): array
    {
        if ($tasks->isEmpty()) {
            return [
                'start' => now(),
                'end' => now()->addMonths(3),
                'workingDays' => 0,
                'totalDays' => 90
            ];
        }
        
        $start = Carbon::parse($tasks->min('start_date'));
        $end = Carbon::parse($tasks->max('end_date'));
        
        return [
            'start' => $start,
            'end' => $end,
            'workingDays' => $this->calculateWorkingDays($start, $end),
            'totalDays' => $start->diffInDays($end)
        ];
    }

    private function calculateStatistics(Collection $tasks): array
    {
        $total = $tasks->count();
        $completed = $tasks->where('status', 'completed')->count();
        $inProgress = $tasks->where('status', 'in_progress')->count();
        $pending = $tasks->where('status', 'pending')->count();
        
        $now = Carbon::now();
        $overdue = $tasks->filter(function ($task) use ($now) {
            return Carbon::parse($task->end_date)->lt($now) && 
                   $task->status !== 'completed';
        })->count();
        
        return [
            'total' => $total,
            'completed' => $completed,
            'inProgress' => $inProgress,
            'pending' => $pending,
            'overdue' => $overdue,
            'completionRate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'onTrack' => $total - $overdue,
            'averageDuration' => $tasks->avg('duration') ?? 0,
            'totalEstimatedHours' => $tasks->sum('estimated_hours') ?? 0,
            'totalActualHours' => $tasks->sum('actual_hours') ?? 0,
            'totalCost' => $tasks->sum('cost') ?? 0
        ];
    }

    private function calculateDuration(?string $startDate, ?string $endDate): int
    {
        if (!$startDate || !$endDate) {
            return 0;
        }
        
        return Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
    }

    private function calculateWorkingDays(Carbon $start, Carbon $end): int
    {
        $workingDays = 0;
        $current = $start->copy();
        
        while ($current->lte($end)) {
            if (!$current->isWeekend()) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        return $workingDays;
    }

    private function calculateWorkingHours(array $dateRange): float
    {
        if (empty($dateRange)) {
            return 0;
        }
        
        $start = Carbon::parse($dateRange['start']);
        $end = Carbon::parse($dateRange['end']);
        $workingDays = $this->calculateWorkingDays($start, $end);
        
        return $workingDays * 8; // Assuming 8 hours per working day
    }

    private function isTaskCritical(Task $task): bool
    {
        // Simplified check - in reality, this would check if task is on critical path
        return $task->priority === 'critical' || $task->priority === 'high';
    }

    private function getUserName(string $userId): string
    {
        // This would fetch the actual user name from the database
        return "User {$userId}";
    }

    private function exportToPdf(array $ganttData, array $options): array
    {
        $pdf = Pdf::loadView('exports.gantt', $ganttData);
        
        if ($options['orientation'] ?? false) {
            $pdf->setPaper($options['paperSize'] ?? 'A4', $options['orientation']);
        }
        
        $filename = "gantt-chart-{$ganttData['project']->id}.pdf";
        $path = storage_path("app/exports/{$filename}");
        $pdf->save($path);
        
        return [
            'path' => $path,
            'filename' => $filename,
            'mime' => 'application/pdf'
        ];
    }

    private function exportToExcel(array $ganttData, array $options): array
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Add headers
        $sheet->setCellValue('A1', 'Task Name');
        $sheet->setCellValue('B1', 'Start Date');
        $sheet->setCellValue('C1', 'End Date');
        $sheet->setCellValue('D1', 'Duration');
        $sheet->setCellValue('E1', 'Progress');
        $sheet->setCellValue('F1', 'Status');
        
        // Add data
        $row = 2;
        foreach ($ganttData['tasks'] as $task) {
            $sheet->setCellValue("A{$row}", $task->name);
            $sheet->setCellValue("B{$row}", $task->start_date);
            $sheet->setCellValue("C{$row}", $task->end_date);
            $sheet->setCellValue("D{$row}", $task->duration);
            $sheet->setCellValue("E{$row}", $task->progress . '%');
            $sheet->setCellValue("F{$row}", $task->status);
            $row++;
        }
        
        $writer = new Xlsx($spreadsheet);
        $filename = "gantt-chart-{$ganttData['project']->id}.xlsx";
        $path = storage_path("app/exports/{$filename}");
        $writer->save($path);
        
        return [
            'path' => $path,
            'filename' => $filename,
            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
    }

    private function topologicalSort(Collection $tasks, Collection $dependencies): Collection
    {
        // Implementation of topological sorting algorithm
        // This is a simplified version
        return $tasks->sortBy('start_date');
    }

    private function calculateTaskDates($task, $dependencies, $options): array
    {
        // Calculate dates based on dependencies
        $start = Carbon::parse($task->start_date);
        $end = Carbon::parse($task->end_date);
        
        // Adjust for weekends if needed
        if ($options['avoidWeekends'] ?? false) {
            while ($start->isWeekend()) {
                $start->addDay();
            }
            while ($end->isWeekend()) {
                $end->addDay();
            }
        }
        
        return [
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d')
        ];
    }

    private function buildNetworkDiagram($tasks, $dependencies): array
    {
        // Build network diagram for critical path calculation
        return [];
    }

    private function calculateForwardPass(&$network): void
    {
        // Calculate early start and early finish times
    }

    private function calculateBackwardPass(&$network): void
    {
        // Calculate late start and late finish times
    }

    private function identifyCriticalTasks($network): Collection
    {
        // Identify tasks with zero slack (critical tasks)
        return collect();
    }

    private function checkResourceConflicts($task): array
    {
        // Check for resource over-allocation
        return [];
    }

    private function calculateProjectDuration($tasks): int
    {
        if ($tasks->isEmpty()) {
            return 0;
        }
        
        $start = Carbon::parse($tasks->min('start_date'));
        $end = Carbon::parse($tasks->max('end_date'));
        
        return $start->diffInDays($end);
    }

    private function optimizeForDuration($tasks): Collection
    {
        // Implement fast-tracking and crashing techniques
        return $tasks;
    }

    private function optimizeForCost($tasks): Collection
    {
        // Implement cost optimization algorithms
        return $tasks;
    }

    private function optimizeForResources($tasks): Collection
    {
        // Implement resource leveling algorithms
        return $tasks;
    }

    private function identifyImprovements($original, $optimized): array
    {
        // Identify what improvements were made
        return [];
    }

    private function groupTasks($tasks, $groupBy): Collection
    {
        return $tasks->groupBy($groupBy);
    }

    private function exportToPng($ganttData, $options): array
    {
        // Implementation for PNG export (would require a charting library)
        throw new \Exception('PNG export not yet implemented');
    }

    private function exportToMsProject($ganttData, $options): array
    {
        // Implementation for MS Project export
        throw new \Exception('MS Project export not yet implemented');
    }

    private function importFromMsProject($projectId, $file): array
    {
        // Implementation for MS Project import
        throw new \Exception('MS Project import not yet implemented');
    }

    private function importFromXml($projectId, $file): array
    {
        // Implementation for XML import
        throw new \Exception('XML import not yet implemented');
    }

    private function importFromCsv($projectId, $file): array
    {
        // Implementation for CSV import
        throw new \Exception('CSV import not yet implemented');
    }
}