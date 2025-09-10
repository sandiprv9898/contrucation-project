<?php

namespace App\Domain\Companies\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CompanyPortfolio extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'category',
        'image_path',
        'external_url',
        'display_order',
        'is_featured',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'metadata' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Storage::url($this->image_path);
    }

    public function getCategoryDisplayAttribute(): string
    {
        $categories = [
            'project' => 'Project',
            'certification' => 'Certification',
            'award' => 'Award',
            'team' => 'Team Member',
            'testimonial' => 'Testimonial',
            'case_study' => 'Case Study',
        ];

        return $categories[$this->category] ?? ucfirst($this->category);
    }

    public function getMetadataAttribute($value): array
    {
        if (is_null($value)) {
            return [];
        }

        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }

        return is_array($value) ? $value : [];
    }

    public function getExcerptAttribute(): string
    {
        if (!$this->description) {
            return '';
        }

        return strlen($this->description) > 150 
            ? substr($this->description, 0, 150) . '...' 
            : $this->description;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('created_at', 'desc');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (is_null($model->display_order)) {
                $maxOrder = static::where('company_id', $model->company_id)
                    ->max('display_order');
                $model->display_order = ($maxOrder ?? 0) + 1;
            }
        });
    }
}