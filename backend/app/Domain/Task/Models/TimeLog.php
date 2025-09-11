<?php

namespace App\Domain\Task\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TimeLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'task_time_logs';
    
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'task_id',
        'user_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'description',
        'billable',
        'hourly_rate',
        'clock_in_location_lat',
        'clock_in_location_lng',
        'clock_in_address',
        'clock_out_location_lat',
        'clock_out_location_lng',
        'clock_out_address',
        'clock_in_photos',
        'clock_out_photos',
        'activity_type',
        'is_active',
    ];

    protected $casts = [
        'id' => 'string',
        'task_id' => 'string',
        'user_id' => 'string',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'billable' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'clock_in_location_lat' => 'decimal:8',
        'clock_in_location_lng' => 'decimal:8',
        'clock_out_location_lat' => 'decimal:8',
        'clock_out_location_lng' => 'decimal:8',
        'clock_in_photos' => 'array',
        'clock_out_photos' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        static::saving(function ($model) {
            // Auto-calculate duration if start and end times are provided
            if ($model->start_time && $model->end_time && !$model->duration_minutes) {
                $model->duration_minutes = $model->start_time->diffInMinutes($model->end_time);
            }
        });
    }

    /**
     * Get the task that this time log belongs to
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Get the user who logged this time
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope to get time logs for a specific task
     */
    public function scopeForTask($query, string $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    /**
     * Scope to get time logs for a specific user
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get billable time logs
     */
    public function scopeBillable($query)
    {
        return $query->where('billable', true);
    }

    /**
     * Scope to get time logs within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_time', [$startDate, $endDate]);
    }

    /**
     * Get the total billable amount for this time log
     */
    public function getBillableAmountAttribute(): float
    {
        if (!$this->billable || !$this->hourly_rate) {
            return 0.0;
        }
        
        return ($this->duration_minutes / 60) * $this->hourly_rate;
    }

    /**
     * Get the duration in hours (formatted)
     */
    public function getDurationHoursAttribute(): float
    {
        return round($this->duration_minutes / 60, 2);
    }

    /**
     * Get formatted duration string (e.g., "2h 30m")
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Check if this time log overlaps with another time period
     */
    public function overlaps($startTime, $endTime): bool
    {
        if (!$this->start_time || !$this->end_time || !$startTime || !$endTime) {
            return false;
        }

        return $this->start_time < $endTime && $this->end_time > $startTime;
    }

    /**
     * Check if the user can edit this time log
     */
    public function canBeEditedBy(User $user): bool
    {
        // User can edit their own time logs within 24 hours of creation
        if ($this->user_id === $user->id) {
            return $this->created_at->diffInHours(now()) <= 24;
        }

        // Managers and admins can always edit
        return $user->hasRole(['admin', 'project_manager', 'supervisor']);
    }

    /**
     * Check if the user can delete this time log
     */
    public function canBeDeletedBy(User $user): bool
    {
        // User can delete their own time logs within 24 hours of creation
        if ($this->user_id === $user->id) {
            return $this->created_at->diffInHours(now()) <= 24;
        }

        // Managers and admins can always delete
        return $user->hasRole(['admin', 'project_manager']);
    }

    /**
     * Get the distance between clock in and clock out locations (in meters)
     */
    public function getLocationDistanceAttribute(): ?float
    {
        if (!$this->clock_in_location_lat || !$this->clock_in_location_lng || 
            !$this->clock_out_location_lat || !$this->clock_out_location_lng) {
            return null;
        }

        return $this->calculateDistance(
            $this->clock_in_location_lat,
            $this->clock_in_location_lng,
            $this->clock_out_location_lat,
            $this->clock_out_location_lng
        );
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Get clock in location as formatted string
     */
    public function getClockInLocationAttribute(): ?string
    {
        if (!$this->clock_in_location_lat || !$this->clock_in_location_lng) {
            return null;
        }

        return $this->clock_in_location_lat . ',' . $this->clock_in_location_lng;
    }

    /**
     * Get clock out location as formatted string
     */
    public function getClockOutLocationAttribute(): ?string
    {
        if (!$this->clock_out_location_lat || !$this->clock_out_location_lng) {
            return null;
        }

        return $this->clock_out_location_lat . ',' . $this->clock_out_location_lng;
    }

    /**
     * Check if this is currently an active time log (clocked in but not out)
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->whereNotNull('start_time')
                    ->whereNull('end_time');
    }

    /**
     * Scope to get time logs with geolocation data
     */
    public function scopeWithLocation($query)
    {
        return $query->where(function($q) {
            $q->whereNotNull('clock_in_location_lat')
              ->orWhereNotNull('clock_out_location_lat');
        });
    }

    /**
     * Scope to get time logs by activity type
     */
    public function scopeByActivityType($query, string $activityType)
    {
        return $query->where('activity_type', $activityType);
    }

    /**
     * Get the formatted activity type
     */
    public function getFormattedActivityTypeAttribute(): string
    {
        $types = [
            'work' => 'Work',
            'break' => 'Break',
            'travel' => 'Travel',
            'meeting' => 'Meeting',
            'inspection' => 'Inspection',
            'planning' => 'Planning',
            'documentation' => 'Documentation',
            'other' => 'Other'
        ];

        return $types[$this->activity_type] ?? ucfirst($this->activity_type);
    }
}