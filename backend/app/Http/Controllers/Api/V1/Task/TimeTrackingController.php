<?php

namespace App\Http\Controllers\Api\V1\Task;

use App\Domain\Task\Models\Task;
use App\Domain\Task\Models\TimeLog;
use App\Domain\Task\Services\TimeTrackingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TimeTrackingController extends Controller
{
    public function __construct(
        private TimeTrackingService $timeTrackingService
    ) {}

    /**
     * Convert string/mixed values to boolean
     */
    private function convertToBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
        }
        
        return (bool) $value;
    }

    /**
     * Get time logs for a task
     */
    public function index(Request $request, string $taskId): JsonResponse
    {
        $task = Task::findOrFail($taskId);
        
        $filters = $request->only([
            'user_id', 'activity_type', 'billable', 
            'date_from', 'date_to'
        ]);

        $timeLogs = $this->timeTrackingService->getTaskTimeLogs($task, $filters);

        return response()->json([
            'data' => $timeLogs,
            'meta' => [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'total_entries' => $timeLogs->count(),
                'total_hours' => round($timeLogs->sum('duration_minutes') / 60, 2),
            ]
        ]);
    }

    /**
     * Get user's time logs
     */
    public function userTimeLogs(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $filters = $request->only([
            'task_id', 'activity_type', 'billable',
            'date_from', 'date_to'
        ]);

        $timeLogs = $this->timeTrackingService->getUserTimeLogs($user, $filters);

        return response()->json([
            'data' => $timeLogs,
            'meta' => [
                'user_id' => $user->id,
                'total_entries' => $timeLogs->count(),
                'total_hours' => round($timeLogs->sum('duration_minutes') / 60, 2),
            ]
        ]);
    }

    /**
     * Clock in to a task
     */
    public function clockIn(Request $request, string $taskId): JsonResponse
    {
        $task = Task::findOrFail($taskId);
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:255',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'activity_type' => 'nullable|string|in:work,break,travel,meeting,inspection,planning,documentation,other',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $timeLog = $this->timeTrackingService->clockIn(
                task: $task,
                user: $user,
                latitude: $request->input('latitude'),
                longitude: $request->input('longitude'),
                address: $request->input('address'),
                photos: $request->file('photos', []),
                activityType: $request->input('activity_type', 'work'),
                description: $request->input('description')
            );

            return response()->json([
                'message' => 'Successfully clocked in',
                'data' => $timeLog
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Clock out from active time log
     */
    public function clockOut(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:255',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'description' => 'nullable|string|max:1000',
            'billable' => 'nullable|in:true,false,1,0,yes,no,on,off',
            'hourly_rate' => 'nullable|numeric|min:0|max:9999.99',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $timeLog = $this->timeTrackingService->clockOut(
                user: $user,
                latitude: $request->input('latitude'),
                longitude: $request->input('longitude'),
                address: $request->input('address'),
                photos: $request->file('photos', []),
                description: $request->input('description'),
                billable: $this->convertToBoolean($request->input('billable', true)),
                hourlyRate: $request->input('hourly_rate')
            );

            return response()->json([
                'message' => 'Successfully clocked out',
                'data' => $timeLog
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get active time log for current user
     */
    public function activeTimeLog(Request $request): JsonResponse
    {
        $user = $request->user();
        $activeTimeLog = $this->timeTrackingService->getActiveTimeLog($user);

        return response()->json([
            'data' => $activeTimeLog
        ]);
    }

    /**
     * Create manual time log entry
     */
    public function store(Request $request, string $taskId): JsonResponse
    {
        $task = Task::findOrFail($taskId);
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'description' => 'required|string|max:1000',
            'activity_type' => 'nullable|string|in:work,break,travel,meeting,inspection,planning,documentation,other',
            'billable' => 'nullable|in:true,false,1,0,yes,no,on,off',
            'hourly_rate' => 'nullable|numeric|min:0|max:9999.99',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $timeLog = $this->timeTrackingService->createManualTimeLog(
                task: $task,
                user: $user,
                startTime: Carbon::parse($request->input('start_time')),
                endTime: Carbon::parse($request->input('end_time')),
                description: $request->input('description'),
                activityType: $request->input('activity_type', 'work'),
                billable: $this->convertToBoolean($request->input('billable', true)),
                hourlyRate: $request->input('hourly_rate')
            );

            return response()->json([
                'message' => 'Time log created successfully',
                'data' => $timeLog
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show specific time log
     */
    public function show(string $timeLogId): JsonResponse
    {
        $timeLog = TimeLog::with(['task', 'user'])->findOrFail($timeLogId);

        return response()->json([
            'data' => $timeLog
        ]);
    }

    /**
     * Update time log
     */
    public function update(Request $request, string $timeLogId): JsonResponse
    {
        $timeLog = TimeLog::findOrFail($timeLogId);
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'description' => 'nullable|string|max:1000',
            'activity_type' => 'nullable|string|in:work,break,travel,meeting,inspection,planning,documentation,other',
            'billable' => 'nullable|in:true,false,1,0,yes,no,on,off',
            'hourly_rate' => 'nullable|numeric|min:0|max:9999.99',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:255',
            'clock_in_photos' => 'nullable|array|max:5',
            'clock_in_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'clock_out_photos' => 'nullable|array|max:5',
            'clock_out_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only([
                'start_time', 'end_time', 'description', 'activity_type',
                'billable', 'hourly_rate', 'clock_in_address', 'clock_out_address'
            ]);

            // Convert billable to boolean if present
            if (isset($data['billable'])) {
                $data['billable'] = $this->convertToBoolean($data['billable']);
            }

            // Handle location updates
            if ($request->has('latitude') && $request->has('longitude')) {
                $data['clock_in_location_lat'] = $request->input('latitude');
                $data['clock_in_location_lng'] = $request->input('longitude');
            }

            if ($request->hasFile('clock_in_photos')) {
                $data['clock_in_photos'] = $request->file('clock_in_photos');
            }

            if ($request->hasFile('clock_out_photos')) {
                $data['clock_out_photos'] = $request->file('clock_out_photos');
            }

            $timeLog = $this->timeTrackingService->updateTimeLog($timeLog, $data, $user);

            return response()->json([
                'message' => 'Time log updated successfully',
                'data' => $timeLog
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete time log
     */
    public function destroy(Request $request, string $timeLogId): JsonResponse
    {
        $timeLog = TimeLog::findOrFail($timeLogId);
        $user = $request->user();

        try {
            $this->timeTrackingService->deleteTimeLog($timeLog, $user);

            return response()->json([
                'message' => 'Time log deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get time tracking statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = $request->only([
            'user_id', 'task_id', 'date_from', 'date_to'
        ]);

        // If no user_id specified, use current user
        if (!isset($filters['user_id'])) {
            $filters['user_id'] = $request->user()->id;
        }

        $stats = $this->timeTrackingService->getTimeTrackingStats($filters);

        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Get activity types
     */
    public function activityTypes(): JsonResponse
    {
        return response()->json([
            'data' => [
                'work' => 'Work',
                'break' => 'Break',
                'travel' => 'Travel',
                'meeting' => 'Meeting',
                'inspection' => 'Inspection',
                'planning' => 'Planning',
                'documentation' => 'Documentation',
                'other' => 'Other',
            ]
        ]);
    }
}