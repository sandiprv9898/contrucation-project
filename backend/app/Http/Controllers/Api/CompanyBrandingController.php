<?php

namespace App\Http\Controllers\Api;

use App\Domain\Companies\Models\CompanyBranding;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyBranding\StoreBrandingAssetRequest;
use App\Http\Requests\CompanyBranding\UpdateBrandingAssetRequest;
use App\Http\Resources\CompanyBrandingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyBrandingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;
        
        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $query = $company->brandingAssets();

        if ($request->has('asset_type')) {
            $query->byType($request->get('asset_type'));
        }

        if ($request->has('asset_variant')) {
            $query->byVariant($request->get('asset_variant'));
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $assets = $query->orderBy('asset_type')
            ->orderBy('asset_variant')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => CompanyBrandingResource::collection($assets)
        ]);
    }

    public function store(StoreBrandingAssetRequest $request): JsonResponse
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

            $file = $request->file('file');
            $assetType = $request->input('asset_type');
            $assetVariant = $request->input('asset_variant');

            $path = $file->store("companies/{$company->id}/branding/{$assetType}", 'public');

            $dimensions = null;
            if (in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                $imageSize = getimagesize($file->getRealPath());
                if ($imageSize) {
                    $dimensions = [
                        'width' => $imageSize[0],
                        'height' => $imageSize[1]
                    ];
                }
            }

            if ($request->boolean('set_as_active')) {
                CompanyBranding::where('company_id', $company->id)
                    ->where('asset_type', $assetType)
                    ->when($assetVariant, function ($query) use ($assetVariant) {
                        return $query->where('asset_variant', $assetVariant);
                    })
                    ->update(['is_active' => false]);
            }

            $asset = CompanyBranding::create([
                'company_id' => $company->id,
                'asset_type' => $assetType,
                'asset_variant' => $assetVariant,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'dimensions' => $dimensions,
                'is_active' => $request->boolean('set_as_active', true)
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Branding asset uploaded successfully',
                'data' => new CompanyBrandingResource($asset)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload branding asset',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(CompanyBranding $brandingAsset): JsonResponse
    {
        $user = request()->user();
        
        if ($brandingAsset->company_id !== $user->company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to branding asset'
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'data' => new CompanyBrandingResource($brandingAsset)
        ]);
    }

    public function update(UpdateBrandingAssetRequest $request, CompanyBranding $brandingAsset): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            
            if ($brandingAsset->company_id !== $user->company->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to branding asset'
                ], Response::HTTP_FORBIDDEN);
            }

            if ($request->boolean('set_as_active') && !$brandingAsset->is_active) {
                CompanyBranding::where('company_id', $brandingAsset->company_id)
                    ->where('asset_type', $brandingAsset->asset_type)
                    ->when($brandingAsset->asset_variant, function ($query) use ($brandingAsset) {
                        return $query->where('asset_variant', $brandingAsset->asset_variant);
                    })
                    ->where('id', '!=', $brandingAsset->id)
                    ->update(['is_active' => false]);
            }

            $brandingAsset->update($request->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Branding asset updated successfully',
                'data' => new CompanyBrandingResource($brandingAsset->fresh())
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update branding asset',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(CompanyBranding $brandingAsset): JsonResponse
    {
        try {
            $user = request()->user();
            
            if ($brandingAsset->company_id !== $user->company->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to branding asset'
                ], Response::HTTP_FORBIDDEN);
            }

            if (Storage::disk('public')->exists($brandingAsset->file_path)) {
                Storage::disk('public')->delete($brandingAsset->file_path);
            }

            $brandingAsset->delete();

            return response()->json([
                'success' => true,
                'message' => 'Branding asset deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete branding asset',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function setActive(Request $request, CompanyBranding $brandingAsset): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            
            if ($brandingAsset->company_id !== $user->company->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to branding asset'
                ], Response::HTTP_FORBIDDEN);
            }

            CompanyBranding::where('company_id', $brandingAsset->company_id)
                ->where('asset_type', $brandingAsset->asset_type)
                ->when($brandingAsset->asset_variant, function ($query) use ($brandingAsset) {
                    return $query->where('asset_variant', $brandingAsset->asset_variant);
                })
                ->update(['is_active' => false]);

            $brandingAsset->update(['is_active' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Branding asset set as active successfully',
                'data' => new CompanyBrandingResource($brandingAsset->fresh())
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to set branding asset as active',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function generateFavicon(Request $request): JsonResponse
    {
        // This would integrate with an image processing service
        // For now, return a placeholder response
        return response()->json([
            'success' => false,
            'message' => 'Favicon generation feature coming soon'
        ], Response::HTTP_NOT_IMPLEMENTED);
    }
}