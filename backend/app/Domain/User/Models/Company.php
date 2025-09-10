<?php

namespace App\Domain\User\Models;

use App\Domain\Companies\Models\CompanyBranding;
use App\Domain\Companies\Models\CompanyPortfolio;
use App\Domain\Companies\Models\CompanyProfile;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'industry',
        'size',
        'address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(CompanyProfile::class);
    }

    public function brandingAssets(): HasMany
    {
        return $this->hasMany(CompanyBranding::class);
    }

    public function activeBrandingAssets(): HasMany
    {
        return $this->hasMany(CompanyBranding::class)->active();
    }

    public function portfolioItems(): HasMany
    {
        return $this->hasMany(CompanyPortfolio::class);
    }

    public function activePortfolioItems(): HasMany
    {
        return $this->hasMany(CompanyPortfolio::class)->active()->ordered();
    }

    public function featuredPortfolioItems(): HasMany
    {
        return $this->hasMany(CompanyPortfolio::class)->featured()->active()->ordered();
    }

    public function getLogo(string $variant = null): ?CompanyBranding
    {
        $query = $this->brandingAssets()->byType('logo')->active();
        
        if ($variant) {
            $query->byVariant($variant);
        }
        
        return $query->first();
    }

    public function getFavicon(): ?CompanyBranding
    {
        return $this->brandingAssets()->byType('favicon')->active()->first();
    }
}