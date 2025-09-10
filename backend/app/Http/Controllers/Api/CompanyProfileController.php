<?php

namespace App\Http\Controllers\Api;

use App\Domain\Companies\Models\CompanyProfile;
use App\Domain\User\Models\Company;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyProfile\StoreCompanyProfileRequest;
use App\Http\Requests\CompanyProfile\UpdateCompanyProfileRequest;
use App\Http\Resources\CompanyProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CompanyProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;
        
        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $profile = $company->profile;
        
        if (!$profile) {
            $profile = new CompanyProfile([
                'company_id' => $company->id
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => new CompanyProfileResource($profile)
        ]);
    }

    public function store(StoreCompanyProfileRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $company = $user->company;

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $existingProfile = $company->profile;
            if ($existingProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company profile already exists. Use update instead.'
                ], Response::HTTP_CONFLICT);
            }

            $profile = CompanyProfile::create([
                'company_id' => $company->id,
                ...$request->validated()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Company profile created successfully',
                'data' => new CompanyProfileResource($profile)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create company profile',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateCompanyProfileRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $company = $user->company;

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $profile = $company->profile;

            if (!$profile) {
                $profile = CompanyProfile::create([
                    'company_id' => $company->id,
                    ...$request->validated()
                ]);
            } else {
                $profile->update($request->validated());
                $profile->refresh();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Company profile updated successfully',
                'data' => new CompanyProfileResource($profile)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update company profile',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $company = $user->company;

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $profile = $company->profile;

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company profile not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $profile->delete();

            return response()->json([
                'success' => true,
                'message' => 'Company profile deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete company profile',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getIndustryTypes(): JsonResponse
    {
        $industryTypes = [
            'construction' => 'Construction',
            'residential' => 'Residential Construction',
            'commercial' => 'Commercial Construction',
            'industrial' => 'Industrial Construction',
            'infrastructure' => 'Infrastructure',
            'renovation' => 'Renovation & Remodeling',
            'landscaping' => 'Landscaping',
            'electrical' => 'Electrical Contracting',
            'plumbing' => 'Plumbing',
            'hvac' => 'HVAC',
            'roofing' => 'Roofing',
            'flooring' => 'Flooring',
            'painting' => 'Painting',
            'concrete' => 'Concrete & Masonry',
            'architecture' => 'Architecture',
            'engineering' => 'Engineering',
            'project_management' => 'Project Management',
            'real_estate' => 'Real Estate Development',
            'other' => 'Other'
        ];

        return response()->json([
            'success' => true,
            'data' => $industryTypes
        ]);
    }
}