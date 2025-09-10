<?php

namespace App\Domain\Companies\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CompanyBranding extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'asset_type',
        'asset_variant',
        'file_path',
        'file_size',
        'mime_type',
        'dimensions',
        'is_active',
    ];

    protected $casts = [
        'dimensions' => 'array',
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDimensionsAttribute($value): array
    {
        if (is_null($value)) {
            return [];
        }

        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }

        return is_array($value) ? $value : [];
    }

    public function getDimensionsStringAttribute(): string
    {
        $dimensions = $this->dimensions;
        
        if (empty($dimensions) || !isset($dimensions['width']) || !isset($dimensions['height'])) {
            return 'Unknown';
        }

        return $dimensions['width'] . ' × ' . $dimensions['height'];
    }

    public function getAssetTypeDisplayAttribute(): string
    {
        $types = [
            'logo' => 'Logo',
            'favicon' => 'Favicon',
            'banner' => 'Banner',
            'watermark' => 'Watermark',
            'icon' => 'Icon',
        ];

        return $types[$this->asset_type] ?? ucfirst($this->asset_type);
    }

    public function getAssetVariantDisplayAttribute(): ?string
    {
        if (!$this->asset_variant) {
            return null;
        }

        $variants = [
            'light' => 'Light Theme',
            'dark' => 'Dark Theme',
            'color' => 'Color',
            'mono' => 'Monochrome',
            'transparent' => 'Transparent',
        ];

        return $variants[$this->asset_variant] ?? ucfirst($this->asset_variant);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('asset_type', $type);
    }

    public function scopeByVariant($query, string $variant)
    {
        return $query->where('asset_variant', $variant);
    }
}