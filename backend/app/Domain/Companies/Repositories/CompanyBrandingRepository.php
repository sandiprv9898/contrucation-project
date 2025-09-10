<?php

namespace App\Domain\Companies\Repositories;

use App\Domain\Companies\Models\CompanyBranding;
use Illuminate\Database\Eloquent\Collection;

class CompanyBrandingRepository implements CompanyBrandingRepositoryInterface
{
    public function getAll(): Collection
    {
        return CompanyBranding::with(['company'])->get();
    }
    
    public function findById(string $id): ?CompanyBranding
    {
        return CompanyBranding::with(['company'])->find($id);
    }
    
    public function getAllByCompany(string $companyId): Collection
    {
        return CompanyBranding::with(['company'])
            ->where('company_id', $companyId)
            ->orderBy('asset_type')
            ->orderBy('asset_variant')
            ->get();
    }
    
    public function getByAssetType(string $companyId, string $assetType): Collection
    {
        return CompanyBranding::with(['company'])
            ->where('company_id', $companyId)
            ->where('asset_type', $assetType)
            ->orderBy('asset_variant')
            ->get();
    }
    
    public function getActiveAsset(string $companyId, string $assetType, ?string $variant = null): ?CompanyBranding
    {
        $query = CompanyBranding::with(['company'])
            ->where('company_id', $companyId)
            ->where('asset_type', $assetType)
            ->where('is_active', true);
            
        if ($variant) {
            $query->where('asset_variant', $variant);
        }
        
        return $query->first();
    }
    
    public function create(array $data): CompanyBranding
    {
        return CompanyBranding::create($data);
    }
    
    public function update(CompanyBranding $branding, array $data): CompanyBranding
    {
        $branding->update($data);
        return $branding->fresh(['company']);
    }
    
    public function delete(CompanyBranding $branding): bool
    {
        return $branding->delete();
    }
    
    public function deactivateAssets(string $companyId, string $assetType, ?string $variant = null): bool
    {
        $query = CompanyBranding::where('company_id', $companyId)
            ->where('asset_type', $assetType);
            
        if ($variant) {
            $query->where('asset_variant', $variant);
        }
        
        return $query->update(['is_active' => false]) !== false;
    }
}