<?php

namespace App\Domain\Settings\Services;

use App\Domain\Settings\DTOs\SettingData;
use App\Domain\Settings\Models\Setting;
use App\Domain\Settings\Repositories\SettingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SettingService
{
    public function __construct(
        private SettingRepositoryInterface $repository
    ) {}

    public function getAllSettings(?string $companyId = null): Collection
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        
        return $this->repository->getAllByCompany($companyId);
    }

    public function getSettingsByCategory(string $category, ?string $companyId = null): Collection
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        
        return $this->repository->getByCategory($category, $companyId);
    }

    public function getSetting(string $category, string $key, $default = null, ?string $companyId = null)
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        
        $setting = $this->repository->getByCategoryAndKey($category, $key, $companyId);
        
        return $setting ? $setting->getValue($default) : $default;
    }

    public function setSetting(string $category, string $key, $value, ?string $companyId = null): Setting
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        
        $this->validateSetting($category, $key, $value);
        
        $existingSetting = $this->repository->getByCategoryAndKey($category, $key, $companyId);
        
        if ($existingSetting) {
            return $this->repository->update($existingSetting, [
                'value' => $value,
                'updated_by' => auth()->id()
            ]);
        }
        
        return $this->repository->create([
            'category' => $category,
            'key' => $key,
            'value' => $value,
            'company_id' => $companyId,
            'updated_by' => auth()->id()
        ]);
    }

    public function updateCategorySettings(string $category, array $settings, ?string $companyId = null): bool
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        
        $settingsData = [];
        foreach ($settings as $key => $value) {
            $this->validateSetting($category, $key, $value);
            
            $settingsData[] = [
                'category' => $category,
                'key' => $key,
                'value' => $value
            ];
        }
        
        return $this->repository->bulkUpdate($settingsData, $companyId);
    }

    public function deleteSetting(string $category, string $key, ?string $companyId = null): bool
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        
        $setting = $this->repository->getByCategoryAndKey($category, $key, $companyId);
        
        if (!$setting) {
            return false;
        }
        
        return $this->repository->delete($setting);
    }

    public function exportSettings(?string $companyId = null): array
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        
        return $this->repository->exportSettings($companyId);
    }

    public function importSettings(array $settings, ?string $companyId = null): bool
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        
        // Validate all settings before import
        foreach ($settings as $setting) {
            $this->validateSetting(
                $setting['category'],
                $setting['key'],
                $setting['value']
            );
        }
        
        return $this->repository->importSettings($settings, $companyId);
    }

    public function getAuditLog(string $category, string $key, ?string $companyId = null): Collection
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        
        $setting = $this->repository->getByCategoryAndKey($category, $key, $companyId);
        
        if (!$setting) {
            return collect();
        }
        
        return $this->repository->getAuditLog($setting->id);
    }

    public function getDefaultSettings(): array
    {
        return [
            'company' => [
                'name' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'logo' => '',
                'primary_color' => '#f97316', // Construction orange
                'secondary_color' => '#475569', // Steel blue
                'currency' => 'USD',
                'tax_rate' => 0,
                'timezone' => 'UTC'
            ],
            'system' => [
                'language' => 'en',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'measurement_unit' => 'metric',
                'default_project_template' => '',
                'auto_backup' => true,
                'maintenance_mode' => false
            ],
            'notifications' => [
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => true,
                'task_updates' => true,
                'project_updates' => true,
                'deadline_reminders' => true,
                'reminder_days' => 3
            ],
            'security' => [
                'password_min_length' => 8,
                'password_require_uppercase' => true,
                'password_require_lowercase' => true,
                'password_require_numbers' => true,
                'password_require_special' => true,
                'two_factor_enabled' => false,
                'session_timeout' => 1440, // 24 hours
                'max_login_attempts' => 5
            ],
            'backup' => [
                'auto_backup_enabled' => true,
                'backup_frequency' => 'daily',
                'backup_time' => '02:00',
                'backup_retention_days' => 30,
                'max_backup_size' => 10,
                'storage_driver' => 'local',
                'compress_backups' => true,
                'enable_backups' => true,
                'backup_path' => '/var/backups/construction-platform',
                // S3 fields
                's3_bucket' => '',
                's3_region' => 'us-east-1',
                's3_access_key' => '',
                's3_secret_key' => '',
                // Recovery options
                'enable_point_in_time_recovery' => false,
                'enable_integrity_checks' => true,
                'recovery_notifications' => true,
                'recovery_window_hours' => 72,
                'max_recovery_attempts' => 3
            ]
        ];
    }

    protected function validateSetting(string $category, string $key, $value): void
    {
        $rules = $this->getValidationRules();
        
        if (!isset($rules[$category][$key])) {
            return; // No validation rules defined
        }
        
        $validator = Validator::make(
            [$key => $value],
            [$key => $rules[$category][$key]]
        );
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function getValidationRules(): array
    {
        return [
            'company' => [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'primary_color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
                'secondary_color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
                'currency' => 'nullable|string|in:USD,EUR,GBP,CAD,AUD',
                'tax_rate' => 'nullable|numeric|min:0|max:100'
            ],
            'system' => [
                'language' => 'required|string|in:en,es,fr,de',
                'date_format' => 'required|string|in:Y-m-d,m/d/Y,d/m/Y',
                'time_format' => 'required|string|in:H:i,h:i A',
                'measurement_unit' => 'required|string|in:metric,imperial'
            ],
            'notifications' => [
                'email_enabled' => 'boolean',
                'sms_enabled' => 'boolean',
                'push_enabled' => 'boolean',
                'reminder_days' => 'integer|min:1|max:30'
            ],
            'security' => [
                'password_min_length' => 'integer|min:6|max:50',
                'session_timeout' => 'integer|min:15|max:43200',
                'max_login_attempts' => 'integer|min:3|max:10'
            ],
            'backup' => [
                'auto_backup_enabled' => 'boolean',
                'backup_frequency' => 'string|in:hourly,daily,weekly',
                'backup_time' => 'string',
                'backup_retention_days' => 'integer|min:1|max:365',
                'max_backup_size' => 'integer|min:1|max:1000',
                'storage_driver' => 'string|in:local,s3,gcs,azure,ftp',
                'compress_backups' => 'boolean',
                'enable_backups' => 'boolean',
                'backup_path' => 'string',
                's3_bucket' => 'string',
                's3_region' => 'string',
                's3_access_key' => 'string',
                's3_secret_key' => 'string',
                'enable_point_in_time_recovery' => 'boolean',
                'enable_integrity_checks' => 'boolean',
                'recovery_notifications' => 'boolean',
                'recovery_window_hours' => 'integer|min:1|max:168',
                'max_recovery_attempts' => 'integer|min:1|max:10'
            ]
        ];
    }

    /**
     * Reset a settings category to default values
     */
    public function resetCategoryToDefaults(string $category, ?string $companyId = null): bool
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        
        // Get default settings for the category
        $defaultSettings = $this->getDefaultSettings();
        
        if (!isset($defaultSettings[$category])) {
            throw new \InvalidArgumentException("Invalid settings category: {$category}");
        }
        
        // Delete all existing settings for this category and company
        $this->repository->deleteCategorySettings($category, $companyId);
        
        // Create new settings with default values
        $settingsData = [];
        foreach ($defaultSettings[$category] as $key => $value) {
            $settingsData[] = [
                'category' => $category,
                'key' => $key,
                'value' => $value
            ];
        }
        
        return $this->repository->bulkUpdate($settingsData, $companyId);
    }
}