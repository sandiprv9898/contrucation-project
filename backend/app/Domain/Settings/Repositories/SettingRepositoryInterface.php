<?php

namespace App\Domain\Settings\Repositories;

use App\Domain\Settings\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

interface SettingRepositoryInterface
{
    public function getAllByCompany(string $companyId): Collection;
    
    public function getByCategory(string $category, string $companyId): Collection;
    
    public function getByCategoryAndKey(string $category, string $key, string $companyId): ?Setting;
    
    public function create(array $data): Setting;
    
    public function update(Setting $setting, array $data): Setting;
    
    public function delete(Setting $setting): bool;
    
    public function bulkUpdate(array $settings, string $companyId): bool;
    
    public function getAuditLog(string $settingId, int $limit = 50): Collection;
    
    public function exportSettings(string $companyId): array;
    
    public function importSettings(array $settings, string $companyId): bool;
    
    public function deleteCategorySettings(string $category, string $companyId): bool;
}