<?php

namespace App\Domain\Companies\Repositories;

use App\Domain\Companies\Models\CompanyProfile;
use Illuminate\Database\Eloquent\Collection;

interface CompanyProfileRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(string $id): ?CompanyProfile;
    
    public function findByCompanyId(string $companyId): ?CompanyProfile;
    
    public function create(array $data): CompanyProfile;
    
    public function update(CompanyProfile $profile, array $data): CompanyProfile;
    
    public function delete(CompanyProfile $profile): bool;
    
    public function getByIndustryType(string $industryType): Collection;
    
    public function getByCompanySize(string $companySize): Collection;
}