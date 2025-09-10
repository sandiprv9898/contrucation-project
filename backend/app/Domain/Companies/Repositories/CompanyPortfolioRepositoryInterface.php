<?php

namespace App\Domain\Companies\Repositories;

use App\Domain\Companies\Models\CompanyPortfolio;
use Illuminate\Database\Eloquent\Collection;

interface CompanyPortfolioRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(string $id): ?CompanyPortfolio;
    
    public function getAllByCompany(string $companyId): Collection;
    
    public function getByCategory(string $companyId, string $category): Collection;
    
    public function getFeaturedItems(string $companyId, int $limit = 5): Collection;
    
    public function create(array $data): CompanyPortfolio;
    
    public function update(CompanyPortfolio $portfolio, array $data): CompanyPortfolio;
    
    public function delete(CompanyPortfolio $portfolio): bool;
    
    public function updateDisplayOrder(string $companyId, array $orderData): bool;
    
    public function getNextDisplayOrder(string $companyId): int;
}