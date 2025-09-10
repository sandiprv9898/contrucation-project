<?php

namespace App\Domain\Companies\Repositories;

use App\Domain\Companies\Models\CompanyProfile;
use Illuminate\Database\Eloquent\Collection;

class CompanyProfileRepository implements CompanyProfileRepositoryInterface
{
    public function getAll(): Collection
    {
        return CompanyProfile::with(['company'])->get();
    }
    
    public function findById(string $id): ?CompanyProfile
    {
        return CompanyProfile::with(['company'])->find($id);
    }
    
    public function findByCompanyId(string $companyId): ?CompanyProfile
    {
        return CompanyProfile::with(['company'])
            ->where('company_id', $companyId)
            ->first();
    }
    
    public function create(array $data): CompanyProfile
    {
        return CompanyProfile::create($data);
    }
    
    public function update(CompanyProfile $profile, array $data): CompanyProfile
    {
        $profile->update($data);
        return $profile->fresh(['company']);
    }
    
    public function delete(CompanyProfile $profile): bool
    {
        return $profile->delete();
    }
    
    public function getByIndustryType(string $industryType): Collection
    {
        return CompanyProfile::with(['company'])
            ->where('industry_type', $industryType)
            ->get();
    }
    
    public function getByCompanySize(string $companySize): Collection
    {
        return CompanyProfile::with(['company'])
            ->where('company_size', $companySize)
            ->get();
    }
}