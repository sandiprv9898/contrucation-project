<?php

namespace App\Domain\Settings\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SettingsAuditLog extends Model
{
    protected $table = 'settings_audit_log';
    
    protected $fillable = [
        'setting_id',
        'category',
        'key',
        'old_value',
        'new_value',
        'changed_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'id' => 'string',
        'setting_id' => 'string',
        'old_value' => 'array',
        'new_value' => 'array',
        'changed_by' => 'string',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // We only use created_at

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    public function setting(): BelongsTo
    {
        return $this->belongsTo(Setting::class, 'setting_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Scope to filter by category
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Scope to filter by date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}