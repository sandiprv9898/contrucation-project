<?php

namespace App\Domain\Companies\Services;

use App\Domain\Companies\DTOs\CreateCompanyBrandingDTO;
use App\Domain\Companies\DTOs\UpdateCompanyBrandingDTO;
use App\Domain\Companies\Models\CompanyBranding;
use App\Domain\Companies\Repositories\CompanyBrandingRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class BrandingService
{
    public function __construct(
        private CompanyBrandingRepositoryInterface $brandingRepository
    ) {}

    public function getCompanyBranding(string $companyId): array
    {
        $cacheKey = "company_branding_{$companyId}";
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($companyId) {
            $branding = $this->brandingRepository->getAllByCompany($companyId);
            
            // Group by asset type for easier frontend consumption
            $grouped = [];
            foreach ($branding as $asset) {
                $grouped[$asset->asset_type][] = [
                    'id' => $asset->id,
                    'variant' => $asset->asset_variant,
                    'file_path' => $asset->file_path,
                    'file_url' => Storage::url($asset->file_path),
                    'file_size' => $asset->file_size,
                    'mime_type' => $asset->mime_type,
                    'dimensions' => $asset->dimensions,
                    'is_active' => $asset->is_active,
                    'created_at' => $asset->created_at,
                ];
            }
            
            return $grouped;
        });
    }

    public function uploadAsset(
        string $companyId, 
        UploadedFile $file, 
        string $assetType, 
        ?string $variant = null
    ): CompanyBranding {
        DB::beginTransaction();
        
        try {
            // Validate file
            $this->validateUploadedFile($file, $assetType);
            
            // Generate file path
            $fileName = $this->generateFileName($file, $assetType, $variant);
            $filePath = "company/{$companyId}/branding/{$fileName}";
            
            // Store file
            $storedPath = $file->storeAs("company/{$companyId}/branding", $fileName, 'public');
            
            // Get file dimensions for images
            $dimensions = null;
            if ($this->isImage($file)) {
                $image = Image::read($file->getRealPath());
                $dimensions = [
                    'width' => $image->width(),
                    'height' => $image->height()
                ];
            }
            
            // Deactivate existing assets of same type and variant if needed
            if ($assetType === 'logo' || $assetType === 'favicon') {
                $this->deactivateExistingAssets($companyId, $assetType, $variant);
            }
            
            // Create branding record
            $brandingData = [
                'company_id' => $companyId,
                'asset_type' => $assetType,
                'asset_variant' => $variant,
                'file_path' => $storedPath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'dimensions' => $dimensions,
                'is_active' => true,
            ];
            
            $branding = $this->brandingRepository->create($brandingData);
            
            DB::commit();
            
            // Clear cache
            $this->clearBrandingCache($companyId);
            
            return $branding;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded file if it exists
            if (isset($storedPath) && Storage::disk('public')->exists($storedPath)) {
                Storage::disk('public')->delete($storedPath);
            }
            
            throw $e;
        }
    }

    public function updateAsset(CompanyBranding $branding, UpdateCompanyBrandingDTO $dto): CompanyBranding
    {
        DB::beginTransaction();
        
        try {
            $updatedBranding = $this->brandingRepository->update($branding, $dto->getNonNullAttributes());
            
            DB::commit();
            
            // Clear cache
            $this->clearBrandingCache($branding->company_id);
            
            return $updatedBranding;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteAsset(CompanyBranding $branding): bool
    {
        DB::beginTransaction();
        
        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($branding->file_path)) {
                Storage::disk('public')->delete($branding->file_path);
            }
            
            $companyId = $branding->company_id;
            $deleted = $this->brandingRepository->delete($branding);
            
            DB::commit();
            
            // Clear cache
            $this->clearBrandingCache($companyId);
            
            return $deleted;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function generateFavicon(string $companyId, CompanyBranding $logoAsset): CompanyBranding
    {
        DB::beginTransaction();
        
        try {
            if (!$this->isImage($logoAsset->mime_type)) {
                throw new \InvalidArgumentException('Logo must be an image to generate favicon');
            }
            
            // Load the original logo
            $logoPath = Storage::disk('public')->path($logoAsset->file_path);
            $image = Image::read($logoPath);
            
            // Resize to 32x32 for favicon
            $favicon = $image->resize(32, 32);
            
            // Generate favicon filename
            $faviconName = 'favicon_' . time() . '.png';
            $faviconPath = "company/{$companyId}/branding/{$faviconName}";
            
            // Save favicon
            $fullPath = Storage::disk('public')->path($faviconPath);
            $favicon->save($fullPath, quality: 90);
            
            // Deactivate existing favicons
            $this->deactivateExistingAssets($companyId, 'favicon');
            
            // Create branding record for favicon
            $brandingData = [
                'company_id' => $companyId,
                'asset_type' => 'favicon',
                'asset_variant' => 'standard',
                'file_path' => $faviconPath,
                'file_size' => filesize($fullPath),
                'mime_type' => 'image/png',
                'dimensions' => ['width' => 32, 'height' => 32],
                'is_active' => true,
            ];
            
            $favicon = $this->brandingRepository->create($brandingData);
            
            DB::commit();
            
            // Clear cache
            $this->clearBrandingCache($companyId);
            
            return $favicon;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getSupportedAssetTypes(): array
    {
        return [
            'logo' => [
                'variants' => ['light', 'dark', 'color', 'mono'],
                'max_size' => 5 * 1024 * 1024, // 5MB
                'mime_types' => ['image/jpeg', 'image/png', 'image/svg+xml'],
                'dimensions' => ['min_width' => 100, 'min_height' => 50]
            ],
            'favicon' => [
                'variants' => ['standard'],
                'max_size' => 1 * 1024 * 1024, // 1MB
                'mime_types' => ['image/png', 'image/x-icon'],
                'dimensions' => ['width' => 32, 'height' => 32]
            ],
            'banner' => [
                'variants' => ['hero', 'email', 'letterhead'],
                'max_size' => 10 * 1024 * 1024, // 10MB
                'mime_types' => ['image/jpeg', 'image/png'],
                'dimensions' => ['min_width' => 800, 'min_height' => 200]
            ],
        ];
    }

    private function validateUploadedFile(UploadedFile $file, string $assetType): void
    {
        $supportedTypes = $this->getSupportedAssetTypes();
        
        if (!isset($supportedTypes[$assetType])) {
            throw new \InvalidArgumentException("Unsupported asset type: {$assetType}");
        }
        
        $config = $supportedTypes[$assetType];
        
        // Check file size
        if ($file->getSize() > $config['max_size']) {
            throw new \InvalidArgumentException("File too large. Maximum size: " . ($config['max_size'] / 1024 / 1024) . "MB");
        }
        
        // Check mime type
        if (!in_array($file->getMimeType(), $config['mime_types'])) {
            throw new \InvalidArgumentException("Invalid file type. Allowed types: " . implode(', ', $config['mime_types']));
        }
        
        // Check dimensions for images
        if ($this->isImage($file) && isset($config['dimensions'])) {
            $image = Image::read($file->getRealPath());
            $width = $image->width();
            $height = $image->height();
            
            if (isset($config['dimensions']['min_width']) && $width < $config['dimensions']['min_width']) {
                throw new \InvalidArgumentException("Image width too small. Minimum: {$config['dimensions']['min_width']}px");
            }
            
            if (isset($config['dimensions']['min_height']) && $height < $config['dimensions']['min_height']) {
                throw new \InvalidArgumentException("Image height too small. Minimum: {$config['dimensions']['min_height']}px");
            }
            
            if (isset($config['dimensions']['width']) && $width !== $config['dimensions']['width']) {
                throw new \InvalidArgumentException("Image width must be exactly {$config['dimensions']['width']}px");
            }
            
            if (isset($config['dimensions']['height']) && $height !== $config['dimensions']['height']) {
                throw new \InvalidArgumentException("Image height must be exactly {$config['dimensions']['height']}px");
            }
        }
    }

    private function generateFileName(UploadedFile $file, string $assetType, ?string $variant): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = time();
        
        if ($variant) {
            return "{$assetType}_{$variant}_{$timestamp}.{$extension}";
        }
        
        return "{$assetType}_{$timestamp}.{$extension}";
    }

    private function isImage($file): bool
    {
        if ($file instanceof UploadedFile) {
            return str_starts_with($file->getMimeType(), 'image/');
        }
        
        if (is_string($file)) {
            return str_starts_with($file, 'image/');
        }
        
        return false;
    }

    private function deactivateExistingAssets(string $companyId, string $assetType, ?string $variant = null): void
    {
        $this->brandingRepository->deactivateAssets($companyId, $assetType, $variant);
    }

    private function clearBrandingCache(string $companyId): void
    {
        Cache::forget("company_branding_{$companyId}");
        Cache::forget("full_company_profile_{$companyId}");
    }
}