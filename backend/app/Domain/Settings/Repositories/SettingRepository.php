<?php

namespace App\Domain\Settings\Repositories;

use App\Domain\Settings\Models\Setting;
use App\Domain\Settings\Models\SettingsAuditLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingRepository implements SettingRepositoryInterface
{
    public function getAllByCompany(string $companyId): Collection
    {
        $cacheKey = "settings.company.{$companyId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($companyId) {
            return Setting::forCompany($companyId)
                ->with(['updatedBy:id,name'])
                ->orderBy('category')
                ->orderBy('key')
                ->get();
        });
    }

    public function getByCategory(string $category, string $companyId): Collection
    {
        $cacheKey = "settings.company.{$companyId}.category.{$category}";
        
        return Cache::remember($cacheKey, 3600, function () use ($category, $companyId) {
            return Setting::forCompany($companyId)
                ->byCategory($category)
                ->with(['updatedBy:id,name'])
                ->orderBy('key')
                ->get();
        });
    }

    public function getByCategoryAndKey(string $category, string $key, string $companyId): ?Setting
    {
        $cacheKey = "settings.company.{$companyId}.{$category}.{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($category, $key, $companyId) {
            return Setting::forCompany($companyId)
                ->byCategory($category)
                ->where('key', $key)
                ->first();
        });
    }

    public function create(array $data): Setting
    {
        $setting = Setting::create($data);
        
        // Clear cache
        $this->clearCache($data['company_id'], $data['category']);
        
        return $setting;
    }

    public function update(Setting $setting, array $data): Setting
    {
        $setting->update($data);
        
        // Clear cache
        $this->clearCache($setting->company_id, $setting->category);
        
        return $setting->fresh();
    }

    public function delete(Setting $setting): bool
    {
        $companyId = $setting->company_id;
        $category = $setting->category;
        
        $result = $setting->delete();
        
        if ($result) {
            $this->clearCache($companyId, $category);
        }
        
        return $result;
    }

    public function bulkUpdate(array $settings, string $companyId): bool
    {
        try {
            DB::beginTransaction();
            
            foreach ($settings as $settingData) {
                Setting::updateOrCreate(
                    [
                        'category' => $settingData['category'],
                        'key' => $settingData['key'],
                        'company_id' => $companyId
                    ],
                    [
                        'value' => $settingData['value'],
                        'updated_by' => auth()->id()
                    ]
                );
            }
            
            DB::commit();
            
            // Clear all cache for company
            $this->clearAllCache($companyId);
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function getAuditLog(string $settingId, int $limit = 50): Collection
    {
        return SettingsAuditLog::where('setting_id', $settingId)
            ->with(['changedBy:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function exportSettings(string $companyId): array
    {
        $settings = $this->getAllByCompany($companyId);
        
        return $settings->map(function ($setting) {
            return [
                'category' => $setting->category,
                'key' => $setting->key,
                'value' => $setting->value,
            ];
        })->toArray();
    }

    public function importSettings(array $settings, string $companyId): bool
    {
        return $this->bulkUpdate($settings, $companyId);
    }

    protected function clearCache(string $companyId, string $category): void
    {
        Cache::forget("settings.company.{$companyId}");
        Cache::forget("settings.company.{$companyId}.category.{$category}");
        
        // Clear individual setting caches for this category
        $pattern = "settings.company.{$companyId}.{$category}.*";
        Cache::flush(); // In production, use more specific cache clearing
    }

    protected function clearAllCache(string $companyId): void
    {
        $patterns = [
            "settings.company.{$companyId}",
            "settings.company.{$companyId}.*"
        ];
        
        Cache::flush(); // In production, use more specific cache clearing
    }

    public function deleteCategorySettings(string $category, string $companyId): bool
    {
        try {
            $deleted = Setting::where('category', $category)
                ->where('company_id', $companyId)
                ->delete();
            
            $this->clearCache($companyId, $category);
            
            return $deleted > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}