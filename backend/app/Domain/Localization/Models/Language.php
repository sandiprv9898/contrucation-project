<?php

namespace App\Domain\Localization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domain\User\Models\User;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag_emoji',
        'direction',
        'date_format',
        'time_format',
        'currency_code',
        'currency_position',
        'thousand_separator',
        'decimal_separator',
        'decimal_places',
        'phone_format',
        'address_format',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'phone_format' => 'array',
        'address_format' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'decimal_places' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get all translations for this language
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    /**
     * Get users who prefer this language
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'preferred_language_id');
    }

    /**
     * Scope to get only active languages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get languages ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get the default language
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first() 
            ?? static::where('code', 'en')->first()
            ?? static::first();
    }

    /**
     * Check if this language is RTL
     */
    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    /**
     * Get formatted currency symbol position
     */
    public function getCurrencyFormat(): array
    {
        return [
            'code' => $this->currency_code,
            'position' => $this->currency_position,
            'symbol' => $this->getCurrencySymbol(),
        ];
    }

    /**
     * Get currency symbol for the currency code
     */
    private function getCurrencySymbol(): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CNY' => '¥',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'CHF' => 'CHF',
            'SAR' => 'ر.س',
            'AED' => 'د.إ',
            'BRL' => 'R$',
            'MXN' => '$',
            'RUB' => '₽',
        ];

        return $symbols[$this->currency_code] ?? $this->currency_code;
    }

    /**
     * Get number format configuration
     */
    public function getNumberFormat(): array
    {
        return [
            'thousand_separator' => $this->thousand_separator,
            'decimal_separator' => $this->decimal_separator,
            'decimal_places' => $this->decimal_places,
        ];
    }

    /**
     * Format number according to language settings
     */
    public function formatNumber(float $number, ?int $decimals = null): string
    {
        $decimals = $decimals ?? $this->decimal_places;
        
        return number_format(
            $number,
            $decimals,
            $this->decimal_separator,
            $this->thousand_separator
        );
    }

    /**
     * Format currency according to language settings
     */
    public function formatCurrency(float $amount, ?int $decimals = null): string
    {
        $formatted = $this->formatNumber($amount, $decimals);
        $symbol = $this->getCurrencySymbol();

        return $this->currency_position === 'before' 
            ? $symbol . $formatted 
            : $formatted . $symbol;
    }

    /**
     * Get date format for JavaScript/frontend
     */
    public function getJsDateFormat(): string
    {
        // Convert PHP date format to JavaScript format
        $formatMap = [
            'MM/DD/YYYY' => 'MM/DD/YYYY',
            'DD/MM/YYYY' => 'DD/MM/YYYY', 
            'DD.MM.YYYY' => 'DD.MM.YYYY',
            'YYYY/MM/DD' => 'YYYY/MM/DD',
            'YYYY-MM-DD' => 'YYYY-MM-DD',
        ];

        return $formatMap[$this->date_format] ?? 'MM/DD/YYYY';
    }

    /**
     * Check if language supports construction terminology
     */
    public function hasConstructionTerms(): bool
    {
        return $this->translations()
            ->whereHas('translationKey', function ($query) {
                $query->where('is_construction_term', true);
            })->exists();
    }

    /**
     * Get completion percentage for this language
     */
    public function getCompletionPercentage(): float
    {
        $totalKeys = TranslationKey::where('requires_localization', true)->count();
        
        if ($totalKeys === 0) {
            return 100.0;
        }

        $translatedKeys = $this->translations()
            ->whereHas('translationKey', function ($query) {
                $query->where('requires_localization', true);
            })
            ->where('status', 'approved')
            ->count();

        return round(($translatedKeys / $totalKeys) * 100, 2);
    }
}