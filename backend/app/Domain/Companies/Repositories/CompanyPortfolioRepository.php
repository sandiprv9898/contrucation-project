<?php

namespace App\Domain\Companies\Repositories;

use App\Domain\Companies\Models\CompanyPortfolio;
use Illuminate\Database\Eloquent\Collection;

class CompanyPortfolioRepository implements CompanyPortfolioRepositoryInterface
{
    public function getAll(): Collection
    {
        return CompanyPortfolio::with(['company'])
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }
    
    public function findById(string $id): ?CompanyPortfolio
    {
        return CompanyPortfolio::with(['company'])->find($id);
    }
    
    public function getAllByCompany(string $companyId): Collection
    {
        return CompanyPortfolio::with(['company'])
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }
    
    public function getByCategory(string $companyId, string $category): Collection
    {
        return CompanyPortfolio::with(['company'])
            ->where('company_id', $companyId)
            ->where('category', $category)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }
    
    public function getFeaturedItems(string $companyId, int $limit = 5): Collection
    {
        return CompanyPortfolio::with(['company'])
            ->where('company_id', $companyId)
            ->where('is_featured', true)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->limit($limit)
            ->get();
    }
    
    public function create(array $data): CompanyPortfolio
    {
        // Auto-assign display order if not provided
        if (!isset($data['display_order'])) {
            $data['display_order'] = $this->getNextDisplayOrder($data['company_id']);
        }
        
        return CompanyPortfolio::create($data);
    }
    
    public function update(CompanyPortfolio $portfolio, array $data): CompanyPortfolio
    {
        $portfolio->update($data);
        return $portfolio->fresh(['company']);
    }
    
    public function delete(CompanyPortfolio $portfolio): bool
    {
        return $portfolio->delete();
    }
    
    public function updateDisplayOrder(string $companyId, array $orderData): bool
    {
        try {
            foreach ($orderData as $item) {
                CompanyPortfolio::where('company_id', $companyId)
                    ->where('id', $item['id'])
                    ->update(['display_order' => $item['order']]);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getNextDisplayOrder(string $companyId): int
    {
        $maxOrder = CompanyPortfolio::where('company_id', $companyId)
            ->max('display_order');
            
        return ($maxOrder ?? 0) + 1;
    }
}