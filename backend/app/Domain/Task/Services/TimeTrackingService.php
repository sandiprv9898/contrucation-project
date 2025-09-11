<?php

namespace App\Domain\Task\Services;

use App\Domain\Task\Models\Task;
use App\Domain\Task\Models\TimeLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TimeTrackingService
{
    /**
     * Clock in to a task with location and photos
     */
    public function clockIn(
        Task $task, 
        User $user, 
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $address = null,
        array $photos = [],
        string $activityType = 'work',
        ?string $description = null
    ): TimeLog {
        // Check if user already has an active time log
        $activeTimeLog = TimeLog::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($activeTimeLog) {
            throw new \Exception('You must clock out from your current task before clocking into a new one.');
        }

        // Handle photo uploads
        $photosPaths = [];
        foreach ($photos as $photo) {
            if ($photo instanceof UploadedFile) {
                $path = $this->storePhoto($photo, 'time-logs/clock-in');
                $photosPaths[] = $path;
            }
        }

        // Create time log
        $timeLog = TimeLog::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'start_time' => now(),
            'clock_in_location_lat' => $latitude,
            'clock_in_location_lng' => $longitude,
            'clock_in_address' => $address,
            'clock_in_photos' => $photosPaths,
            'activity_type' => $activityType,
            'description' => $description,
            'is_active' => true,
            'duration_minutes' => 0,
        ]);

        return $timeLog->load(['task', 'user']);
    }

    /**
     * Clock out from active time log
     */
    public function clockOut(
        User $user,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $address = null,
        array $photos = [],
        ?string $description = null,
        bool $billable = true,
        ?float $hourlyRate = null
    ): TimeLog {
        $activeTimeLog = TimeLog::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$activeTimeLog) {
            throw new \Exception('No active time log found to clock out from.');
        }

        // Handle photo uploads
        $photosPaths = [];
        foreach ($photos as $photo) {
            if ($photo instanceof UploadedFile) {
                $path = $this->storePhoto($photo, 'time-logs/clock-out');
                $photosPaths[] = $path;
            }
        }

        $endTime = now();
        $durationMinutes = $activeTimeLog->start_time->diffInMinutes($endTime);

        // Update time log
        $activeTimeLog->update([
            'end_time' => $endTime,
            'duration_minutes' => $durationMinutes,
            'clock_out_location_lat' => $latitude,
            'clock_out_location_lng' => $longitude,
            'clock_out_address' => $address,
            'clock_out_photos' => $photosPaths,
            'description' => $description ?: $activeTimeLog->description,
            'billable' => $billable,
            'hourly_rate' => $hourlyRate,
            'is_active' => false,
        ]);

        // Update task actual hours
        $this->updateTaskActualHours($activeTimeLog->task);

        return $activeTimeLog->fresh()->load(['task', 'user']);
    }

    /**
     * Get active time log for user
     */
    public function getActiveTimeLog(User $user): ?TimeLog
    {
        return TimeLog::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['task', 'user'])
            ->first();
    }

    /**
     * Get time logs for a task
     */
    public function getTaskTimeLogs(Task $task, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = TimeLog::where('task_id', $task->id)
            ->with(['user'])
            ->orderBy('start_time', 'desc');

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['activity_type'])) {
            $query->where('activity_type', $filters['activity_type']);
        }

        if (isset($filters['billable'])) {
            $query->where('billable', $filters['billable']);
        }

        if (isset($filters['date_from'])) {
            $query->where('start_time', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('start_time', '<=', $filters['date_to']);
        }

        return $query->get();
    }

    /**
     * Get user time logs
     */
    public function getUserTimeLogs(User $user, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = TimeLog::where('user_id', $user->id)
            ->with(['task'])
            ->orderBy('start_time', 'desc');

        // Apply filters similar to getTaskTimeLogs
        if (isset($filters['task_id'])) {
            $query->where('task_id', $filters['task_id']);
        }

        if (isset($filters['activity_type'])) {
            $query->where('activity_type', $filters['activity_type']);
        }

        if (isset($filters['date_from'])) {
            $query->where('start_time', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('start_time', '<=', $filters['date_to']);
        }

        return $query->get();
    }

    /**
     * Create manual time log entry
     */
    public function createManualTimeLog(
        Task $task,
        User $user,
        Carbon $startTime,
        Carbon $endTime,
        string $description,
        string $activityType = 'work',
        bool $billable = true,
        ?float $hourlyRate = null
    ): TimeLog {
        $durationMinutes = $startTime->diffInMinutes($endTime);

        // Check for overlapping time logs
        $overlapping = TimeLog::where('user_id', $user->id)
            ->where(function($query) use ($startTime, $endTime) {
                $query->where(function($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->exists();

        if ($overlapping) {
            throw new \Exception('Time log overlaps with existing time entries.');
        }

        $timeLog = TimeLog::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $durationMinutes,
            'description' => $description,
            'activity_type' => $activityType,
            'billable' => $billable,
            'hourly_rate' => $hourlyRate,
            'is_active' => false,
        ]);

        // Update task actual hours
        $this->updateTaskActualHours($task);

        return $timeLog->load(['task', 'user']);
    }

    /**
     * Update time log
     */
    public function updateTimeLog(
        TimeLog $timeLog,
        array $data,
        User $user
    ): TimeLog {
        if (!$timeLog->canBeEditedBy($user)) {
            throw new \Exception('You do not have permission to edit this time log.');
        }

        // Handle photo uploads if provided
        if (isset($data['clock_in_photos'])) {
            $photosPaths = [];
            foreach ($data['clock_in_photos'] as $photo) {
                if ($photo instanceof UploadedFile) {
                    $path = $this->storePhoto($photo, 'time-logs/clock-in');
                    $photosPaths[] = $path;
                }
            }
            $data['clock_in_photos'] = $photosPaths;
        }

        if (isset($data['clock_out_photos'])) {
            $photosPaths = [];
            foreach ($data['clock_out_photos'] as $photo) {
                if ($photo instanceof UploadedFile) {
                    $path = $this->storePhoto($photo, 'time-logs/clock-out');
                    $photosPaths[] = $path;
                }
            }
            $data['clock_out_photos'] = $photosPaths;
        }

        // Recalculate duration if times changed
        if (isset($data['start_time']) || isset($data['end_time'])) {
            $startTime = isset($data['start_time']) ? Carbon::parse($data['start_time']) : $timeLog->start_time;
            $endTime = isset($data['end_time']) ? Carbon::parse($data['end_time']) : $timeLog->end_time;
            
            if ($startTime && $endTime) {
                $data['duration_minutes'] = $startTime->diffInMinutes($endTime);
            }
        }

        $timeLog->update($data);

        // Update task actual hours
        $this->updateTaskActualHours($timeLog->task);

        return $timeLog->fresh()->load(['task', 'user']);
    }

    /**
     * Delete time log
     */
    public function deleteTimeLog(TimeLog $timeLog, User $user): bool
    {
        if (!$timeLog->canBeDeletedBy($user)) {
            throw new \Exception('You do not have permission to delete this time log.');
        }

        $task = $timeLog->task;
        
        // Delete associated photos
        $this->deleteTimeLogPhotos($timeLog);
        
        $timeLog->delete();

        // Update task actual hours
        $this->updateTaskActualHours($task);

        return true;
    }

    /**
     * Get time tracking statistics
     */
    public function getTimeTrackingStats(array $filters = []): array
    {
        $query = TimeLog::query();

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['task_id'])) {
            $query->where('task_id', $filters['task_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('start_time', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('start_time', '<=', $filters['date_to']);
        }

        $timeLogs = $query->get();

        return [
            'total_entries' => $timeLogs->count(),
            'total_hours' => round($timeLogs->sum('duration_minutes') / 60, 2),
            'billable_hours' => round($timeLogs->where('billable', true)->sum('duration_minutes') / 60, 2),
            'total_earnings' => $timeLogs->sum('billable_amount'),
            'activity_breakdown' => $timeLogs->groupBy('activity_type')->map(function ($logs) {
                return [
                    'count' => $logs->count(),
                    'hours' => round($logs->sum('duration_minutes') / 60, 2),
                ];
            })->toArray(),
            'daily_hours' => $timeLogs->groupBy(function($item) {
                return $item->start_time->format('Y-m-d');
            })->map(function ($logs) {
                return round($logs->sum('duration_minutes') / 60, 2);
            })->toArray(),
        ];
    }

    /**
     * Store uploaded photo
     */
    private function storePhoto(UploadedFile $photo, string $directory): string
    {
        $filename = Str::uuid() . '.' . $photo->getClientOriginalExtension();
        $path = $photo->storeAs($directory, $filename, 'public');
        
        return $path;
    }

    /**
     * Delete time log photos
     */
    private function deleteTimeLogPhotos(TimeLog $timeLog): void
    {
        $photos = array_merge(
            $timeLog->clock_in_photos ?? [],
            $timeLog->clock_out_photos ?? []
        );

        foreach ($photos as $photo) {
            Storage::disk('public')->delete($photo);
        }
    }

    /**
     * Update task actual hours based on time logs
     */
    private function updateTaskActualHours(Task $task): void
    {
        $totalMinutes = TimeLog::where('task_id', $task->id)
            ->where('is_active', false)
            ->sum('duration_minutes');

        $task->update([
            'actual_hours' => round($totalMinutes / 60, 2)
        ]);
    }
}