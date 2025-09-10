<?php

namespace App\Domain\Settings\Models;

use App\Domain\User\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'key',
        'value',
        'company_id',
        'updated_by'
    ];

    protected $casts = [
        'id' => 'string',
        'value' => 'array',
        'company_id' => 'string',
        'updated_by' => 'string',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });

        static::updating(function ($setting) {
            // Log audit trail before updating
            if ($setting->isDirty('value')) {
                $setting->logAuditTrail($setting->getOriginal('value'), $setting->value);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(SettingsAuditLog::class, 'setting_id');
    }

    // Scope to filter by company
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // Scope to filter by category
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Get setting value with type casting
    public function getValue($default = null)
    {
        return $this->value ?? $default;
    }

    // Set setting value
    public function setValue($value): void
    {
        $this->value = $value;
    }

    // Log audit trail
    protected function logAuditTrail($oldValue, $newValue): void
    {
        SettingsAuditLog::create([
            'setting_id' => $this->id,
            'category' => $this->category,
            'key' => $this->key,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // Static method to get setting value
    public static function get(string $category, string $key, $default = null, ?string $companyId = null)
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        
        $setting = static::where('category', $category)
            ->where('key', $key)
            ->where('company_id', $companyId)
            ->first();

        return $setting ? $setting->getValue($default) : $default;
    }

    // Static method to set setting value
    public static function set(string $category, string $key, $value, ?string $companyId = null): Setting
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        
        return static::updateOrCreate(
            [
                'category' => $category,
                'key' => $key,
                'company_id' => $companyId
            ],
            [
                'value' => $value,
                'updated_by' => auth()->id()
            ]
        );
    }
}