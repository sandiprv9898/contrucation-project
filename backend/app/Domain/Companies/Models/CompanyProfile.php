<?php

namespace App\Domain\Companies\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProfile extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'business_registration',
        'tax_identification',
        'industry_type',
        'company_size',
        'founded_date',
        'description',
        'website',
        'social_media',
        'certifications',
    ];

    protected $casts = [
        'founded_date' => 'date',
        'social_media' => 'array',
        'certifications' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getSocialMediaAttribute($value): array
    {
        if (is_null($value)) {
            return [];
        }

        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }

        return is_array($value) ? $value : [];
    }

    public function getCertificationsAttribute($value): array
    {
        if (is_null($value)) {
            return [];
        }

        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }

        return is_array($value) ? $value : [];
    }

    public function getCompanySizeDisplayAttribute(): string
    {
        $sizes = [
            'startup' => 'Startup (1-10 employees)',
            'small' => 'Small (11-50 employees)',
            'medium' => 'Medium (51-200 employees)',
            'large' => 'Large (201-1000 employees)',
            'enterprise' => 'Enterprise (1000+ employees)',
        ];

        return $sizes[$this->company_size] ?? ucfirst($this->company_size);
    }
}