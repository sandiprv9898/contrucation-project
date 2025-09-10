<?php

namespace App\Domain\Companies\Services;

use App\Domain\Companies\DTOs\CreateCompanyPortfolioDTO;
use App\Domain\Companies\DTOs\UpdateCompanyPortfolioDTO;
use App\Domain\Companies\Models\CompanyPortfolio;
use App\Domain\Companies\Repositories\CompanyPortfolioRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class CompanyPortfolioService
{
    public function __construct(
        private CompanyPortfolioRepositoryInterface $portfolioRepository
    ) {}

    public function getCompanyPortfolio(string $companyId, ?string $category = null): array
    {
        $cacheKey = $category 
            ? "company_portfolio_{$companyId}_{$category}"
            : "company_portfolio_{$companyId}";
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($companyId, $category) {
            $portfolio = $category 
                ? $this->portfolioRepository->getByCategory($companyId, $category)
                : $this->portfolioRepository->getAllByCompany($companyId);
            
            return $portfolio->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'category' => $item->category,
                    'image_url' => $item->image_path ? Storage::url($item->image_path) : null,
                    'external_url' => $item->external_url,
                    'display_order' => $item->display_order,
                    'is_featured' => $item->is_featured,
                    'is_active' => $item->is_active,
                    'metadata' => $item->metadata,
                    'created_at' => $item->created_at,
                ];
            })->values()->toArray();
        });
    }

    public function getFeaturedItems(string $companyId, int $limit = 5): array
    {
        $cacheKey = "company_featured_portfolio_{$companyId}_{$limit}";
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($companyId, $limit) {
            $featured = $this->portfolioRepository->getFeaturedItems($companyId, $limit);
            
            return $featured->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'category' => $item->category,
                    'image_url' => $item->image_path ? Storage::url($item->image_path) : null,
                    'external_url' => $item->external_url,
                    'metadata' => $item->metadata,
                ];
            })->values()->toArray();
        });
    }

    public function createPortfolioItem(string $companyId, CreateCompanyPortfolioDTO $dto, ?UploadedFile $image = null): CompanyPortfolio
    {
        DB::beginTransaction();
        
        try {
            $itemData = $dto->toArray();
            $itemData['company_id'] = $companyId;
            
            // Handle image upload if provided
            if ($image) {
                $imagePath = $this->uploadPortfolioImage($companyId, $image);
                $itemData['image_path'] = $imagePath;
            }
            
            $portfolioItem = $this->portfolioRepository->create($itemData);
            
            DB::commit();
            
            // Clear cache
            $this->clearPortfolioCache($companyId);
            
            return $portfolioItem;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded image if it exists
            if (isset($imagePath) && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
            throw $e;
        }
    }

    public function updatePortfolioItem(CompanyPortfolio $portfolio, UpdateCompanyPortfolioDTO $dto, ?UploadedFile $image = null): CompanyPortfolio
    {
        DB::beginTransaction();
        
        try {
            $updateData = $dto->getNonNullAttributes();
            
            // Handle image upload if provided
            if ($image) {
                // Delete old image if exists
                if ($portfolio->image_path && Storage::disk('public')->exists($portfolio->image_path)) {
                    Storage::disk('public')->delete($portfolio->image_path);
                }
                
                $imagePath = $this->uploadPortfolioImage($portfolio->company_id, $image);
                $updateData['image_path'] = $imagePath;
            }
            
            $updatedPortfolio = $this->portfolioRepository->update($portfolio, $updateData);
            
            DB::commit();
            
            // Clear cache
            $this->clearPortfolioCache($portfolio->company_id);
            
            return $updatedPortfolio;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded image if it exists
            if (isset($imagePath) && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
            throw $e;
        }
    }

    public function deletePortfolioItem(CompanyPortfolio $portfolio): bool
    {
        DB::beginTransaction();
        
        try {
            // Delete associated image if exists
            if ($portfolio->image_path && Storage::disk('public')->exists($portfolio->image_path)) {
                Storage::disk('public')->delete($portfolio->image_path);
            }
            
            $companyId = $portfolio->company_id;
            $deleted = $this->portfolioRepository->delete($portfolio);
            
            DB::commit();
            
            // Clear cache
            $this->clearPortfolioCache($companyId);
            
            return $deleted;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function reorderPortfolioItems(string $companyId, array $orderData): bool
    {
        DB::beginTransaction();
        
        try {
            $updated = $this->portfolioRepository->updateDisplayOrder($companyId, $orderData);
            
            if ($updated) {
                DB::commit();
                
                // Clear cache
                $this->clearPortfolioCache($companyId);
                
                return true;
            }
            
            DB::rollBack();
            return false;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getPortfolioCategories(): array
    {
        return [
            'project' => 'Project',
            'certification' => 'Certification',
            'award' => 'Award',
            'team' => 'Team Member',
            'testimonial' => 'Testimonial',
            'case_study' => 'Case Study',
            'gallery' => 'Photo Gallery'
        ];
    }

    private function uploadPortfolioImage(string $companyId, UploadedFile $image): string
    {
        // Validate image
        $this->validatePortfolioImage($image);
        
        // Generate filename
        $filename = 'portfolio_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $path = "company/{$companyId}/portfolio/{$filename}";
        
        // Resize and optimize image
        $imageResource = Image::read($image->getRealPath());
        
        // Resize if too large (max 1920x1080)
        if ($imageResource->width() > 1920 || $imageResource->height() > 1080) {
            $imageResource->scaleDown(1920, 1080);
        }
        
        // Save optimized image
        $fullPath = Storage::disk('public')->path($path);
        $imageResource->save($fullPath, quality: 85);
        
        return $path;
    }

    private function validatePortfolioImage(UploadedFile $image): void
    {
        $maxSize = 10 * 1024 * 1024; // 10MB
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        
        if ($image->getSize() > $maxSize) {
            throw new \InvalidArgumentException("Image too large. Maximum size: 10MB");
        }
        
        if (!in_array($image->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException("Invalid image format. Allowed: JPEG, PNG, WebP");
        }
        
        // Check image dimensions
        $imageInfo = getimagesize($image->getRealPath());
        if ($imageInfo === false) {
            throw new \InvalidArgumentException("Invalid image file");
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        if ($width < 300 || $height < 200) {
            throw new \InvalidArgumentException("Image too small. Minimum size: 300x200 pixels");
        }
    }

    private function clearPortfolioCache(string $companyId): void
    {
        Cache::forget("company_portfolio_{$companyId}");
        Cache::forget("company_featured_portfolio_{$companyId}_5");
        Cache::forget("full_company_profile_{$companyId}");
        
        // Clear category-specific caches
        $categories = array_keys($this->getPortfolioCategories());
        foreach ($categories as $category) {
            Cache::forget("company_portfolio_{$companyId}_{$category}");
        }
    }
}