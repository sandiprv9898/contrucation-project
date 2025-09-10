<?php

namespace App\Domain\Companies\Services;

use App\Domain\Companies\DTOs\CreateCompanyProfileDTO;
use App\Domain\Companies\DTOs\UpdateCompanyProfileDTO;
use App\Domain\Companies\Models\Company;
use App\Domain\Companies\Models\CompanyProfile;
use App\Domain\Companies\Repositories\CompanyProfileRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CompanyProfileService
{
    public function __construct(
        private CompanyProfileRepositoryInterface $companyProfileRepository
    ) {}

    public function getCompanyProfile(string $companyId): ?CompanyProfile
    {
        $cacheKey = "company_profile_{$companyId}";
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($companyId) {
            return $this->companyProfileRepository->findByCompanyId($companyId);
        });
    }

    public function createOrUpdateProfile(string $companyId, CreateCompanyProfileDTO|UpdateCompanyProfileDTO $dto): CompanyProfile
    {
        DB::beginTransaction();
        
        try {
            $existingProfile = $this->companyProfileRepository->findByCompanyId($companyId);
            
            if ($existingProfile) {
                $profile = $this->companyProfileRepository->update($existingProfile, $dto->toArray());
            } else {
                $profileData = $dto->toArray();
                $profileData['company_id'] = $companyId;
                $profile = $this->companyProfileRepository->create($profileData);
            }
            
            DB::commit();
            
            // Clear cache
            $this->clearCompanyProfileCache($companyId);
            
            return $profile;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateProfile(CompanyProfile $profile, UpdateCompanyProfileDTO $dto): CompanyProfile
    {
        DB::beginTransaction();
        
        try {
            $updatedProfile = $this->companyProfileRepository->update($profile, $dto->getNonNullAttributes());
            
            DB::commit();
            
            // Clear cache
            $this->clearCompanyProfileCache($profile->company_id);
            
            return $updatedProfile;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getIndustryTypes(): array
    {
        return [
            'construction' => 'Construction',
            'architecture' => 'Architecture',
            'engineering' => 'Engineering',
            'real_estate' => 'Real Estate Development',
            'infrastructure' => 'Infrastructure',
            'residential' => 'Residential Construction',
            'commercial' => 'Commercial Construction',
            'industrial' => 'Industrial Construction',
            'civil' => 'Civil Engineering',
            'mechanical' => 'Mechanical Engineering',
            'electrical' => 'Electrical Engineering',
            'plumbing' => 'Plumbing & HVAC',
            'landscaping' => 'Landscaping',
            'interior_design' => 'Interior Design',
            'project_management' => 'Project Management',
            'consulting' => 'Construction Consulting',
            'other' => 'Other'
        ];
    }

    public function getCompanySizeOptions(): array
    {
        return [
            'startup' => '1-10 employees',
            'small' => '11-50 employees', 
            'medium' => '51-200 employees',
            'large' => '201-1000 employees',
            'enterprise' => '1000+ employees'
        ];
    }

    public function deleteProfile(CompanyProfile $profile): bool
    {
        DB::beginTransaction();
        
        try {
            $companyId = $profile->company_id;
            $deleted = $this->companyProfileRepository->delete($profile);
            
            DB::commit();
            
            // Clear cache
            $this->clearCompanyProfileCache($companyId);
            
            return $deleted;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getFullCompanyProfile(string $companyId): array
    {
        $cacheKey = "full_company_profile_{$companyId}";
        
        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($companyId) {
            $company = Company::with(['profile', 'branding', 'portfolio'])->find($companyId);
            
            if (!$company) {
                return [];
            }

            return [
                'basic_info' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'email' => $company->email,
                    'created_at' => $company->created_at,
                    'updated_at' => $company->updated_at,
                ],
                'profile' => $company->profile ? [
                    'business_registration' => $company->profile->business_registration,
                    'tax_identification' => $company->profile->tax_identification,
                    'industry_type' => $company->profile->industry_type,
                    'company_size' => $company->profile->company_size,
                    'founded_date' => $company->profile->founded_date,
                    'description' => $company->profile->description,
                    'website' => $company->profile->website,
                    'social_media' => $company->profile->social_media,
                    'certifications' => $company->profile->certifications,
                ] : null,
                'branding' => $company->branding->groupBy('asset_type'),
                'portfolio' => $company->portfolio->where('is_active', true)
                    ->sortBy('display_order')
                    ->values()
            ];
        });
    }

    private function clearCompanyProfileCache(string $companyId): void
    {
        Cache::forget("company_profile_{$companyId}");
        Cache::forget("full_company_profile_{$companyId}");
    }
}