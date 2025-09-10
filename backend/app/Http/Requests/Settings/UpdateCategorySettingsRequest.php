<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategorySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        $category = $this->route('category');
        
        // Role-based authorization per category
        return match($category) {
            'company' => in_array($user->role, ['admin']),
            'system' => in_array($user->role, ['admin']),
            'notifications' => in_array($user->role, ['admin', 'manager']),
            'security' => in_array($user->role, ['admin']),
            'backup' => in_array($user->role, ['admin']),
            default => false,
        };
    }

    public function rules(): array
    {
        $category = $this->route('category');
        
        $rules = [
            'settings' => 'required|array',
        ];
        
        // Category-specific validation rules
        if ($category === 'backup') {
            $rules = array_merge($rules, $this->backupValidationRules());
        } else {
            $rules['settings.*'] = 'required';
        }
        
        return $rules;
    }
    
    protected function backupValidationRules(): array
    {
        $storageDriver = $this->input('settings.storage_driver', 'local');
        
        $rules = [
            'settings.auto_backup_enabled' => 'boolean',
            'settings.backup_frequency' => 'string|in:hourly,daily,weekly',
            'settings.backup_time' => 'string',
            'settings.backup_retention_days' => 'integer|min:1|max:365',
            'settings.max_backup_size' => 'integer|min:1|max:1000',
            'settings.storage_driver' => 'required|string|in:local,s3,gcs,azure,ftp',
            'settings.compress_backups' => 'boolean',
            'settings.enable_backups' => 'boolean',
            'settings.backup_path' => 'string',
            'settings.enable_point_in_time_recovery' => 'boolean',
            'settings.enable_integrity_checks' => 'boolean',
            'settings.recovery_notifications' => 'boolean',
            'settings.recovery_window_hours' => 'integer|min:1|max:168',
            'settings.max_recovery_attempts' => 'integer|min:1|max:10',
        ];
        
        // Conditional validation based on storage driver
        if ($storageDriver === 's3') {
            $rules = array_merge($rules, [
                'settings.s3_bucket' => 'required|string',
                'settings.s3_access_key' => 'required|string',
                'settings.s3_secret_key' => 'required|string',
                'settings.s3_region' => 'required|string',
            ]);
        }
        
        if ($storageDriver === 'gcs') {
            $rules = array_merge($rules, [
                'settings.gcs_bucket' => 'required|string',
                'settings.gcs_credentials' => 'required|string',
            ]);
        }
        
        if ($storageDriver === 'local') {
            $rules = array_merge($rules, [
                'settings.backup_path' => 'string',
            ]);
        }
        
        return $rules;
    }

    public function messages(): array
    {
        return [
            'settings.required' => 'Settings data is required.',
            'settings.array' => 'Settings must be an array.',
            'settings.storage_driver.required' => 'Storage driver is required.',
            'settings.storage_driver.in' => 'Storage driver must be one of: local, s3, gcs, azure, ftp.',
            'settings.s3_bucket.required' => 'S3 bucket name is required when using S3 storage.',
            'settings.s3_access_key.required' => 'S3 access key is required when using S3 storage.',
            'settings.s3_secret_key.required' => 'S3 secret key is required when using S3 storage.',
            'settings.s3_region.required' => 'S3 region is required when using S3 storage.',
            'settings.gcs_bucket.required' => 'GCS bucket name is required when using Google Cloud storage.',
            'settings.gcs_credentials.required' => 'GCS credentials are required when using Google Cloud storage.',
            'settings.backup_frequency.in' => 'Backup frequency must be hourly, daily, or weekly.',
            'settings.backup_retention_days.min' => 'Backup retention must be at least 1 day.',
            'settings.backup_retention_days.max' => 'Backup retention cannot exceed 365 days.',
        ];
    }
}