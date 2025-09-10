<?php

namespace App\Domain\Companies\Repositories;

use App\Domain\Companies\Models\CompanyBranding;
use Illuminate\Database\Eloquent\Collection;

interface CompanyBrandingRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(string $id): ?CompanyBranding;
    
    public function getAllByCompany(string $companyId): Collection;
    
    public function getByAssetType(string $companyId, string $assetType): Collection;
    
    public function getActiveAsset(string $companyId, string $assetType, ?string $variant = null): ?CompanyBranding;
    
    public function create(array $data): CompanyBranding;
    
    public function update(CompanyBranding $branding, array $data): CompanyBranding;
    
    public function delete(CompanyBranding $branding): bool;
    
    public function deactivateAssets(string $companyId, string $assetType, ?string $variant = null): bool;
}