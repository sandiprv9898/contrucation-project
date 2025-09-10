<?php

namespace App\Domain\Localization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TranslationKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'namespace',
        'group',
        'key',
        'description',
        'context',
        'type',
        'is_construction_term',
        'requires_localization',
    ];

    protected $casts = [
        'context' => 'array',
        'is_construction_term' => 'boolean',
        'requires_localization' => 'boolean',
    ];

    /**
     * Get all translations for this key
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    /**
     * Get translation for specific language
     */
    public function getTranslation(Language $language): ?Translation
    {
        return $this->translations()
            ->where('language_id', $language->id)
            ->where('status', 'approved')
            ->first();
    }

    /**
     * Get translation value for specific language
     */
    public function getTranslationValue(Language $language, ?string $fallback = null): string
    {
        $translation = $this->getTranslation($language);
        
        if ($translation) {
            return $translation->value;
        }

        // Try to get English translation as fallback
        if ($language->code !== 'en') {
            $englishLanguage = Language::where('code', 'en')->first();
            if ($englishLanguage) {
                $englishTranslation = $this->getTranslation($englishLanguage);
                if ($englishTranslation) {
                    return $englishTranslation->value;
                }
            }
        }

        return $fallback ?? $this->getFullKey();
    }

    /**
     * Get the full dot-notation key
     */
    public function getFullKey(): string
    {
        $parts = array_filter([
            $this->namespace,
            $this->group,
            $this->key
        ]);

        return implode('.', $parts);
    }

    /**
     * Scope to get construction terms only
     */
    public function scopeConstructionTerms($query)
    {
        return $query->where('is_construction_term', true);
    }

    /**
     * Scope to filter by namespace
     */
    public function scopeInNamespace($query, string $namespace)
    {
        return $query->where('namespace', $namespace);
    }

    /**
     * Scope to filter by group
     */
    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to get keys that require localization
     */
    public function scopeRequiresLocalization($query)
    {
        return $query->where('requires_localization', true);
    }

    /**
     * Check if key has translation in specific language
     */
    public function hasTranslationIn(Language $language): bool
    {
        return $this->translations()
            ->where('language_id', $language->id)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Get missing languages for this key
     */
    public function getMissingLanguages()
    {
        $translatedLanguageIds = $this->translations()
            ->where('status', 'approved')
            ->pluck('language_id');

        return Language::active()
            ->whereNotIn('id', $translatedLanguageIds)
            ->get();
    }

    /**
     * Get completion status for this key
     */
    public function getCompletionStatus(): array
    {
        $activeLanguages = Language::active()->get();
        $translations = $this->translations()
            ->where('status', 'approved')
            ->get()
            ->keyBy('language_id');

        $status = [];
        foreach ($activeLanguages as $language) {
            $status[$language->code] = [
                'language_id' => $language->id,
                'language_name' => $language->name,
                'has_translation' => isset($translations[$language->id]),
                'translation_value' => $translations[$language->id]->value ?? null,
            ];
        }

        return $status;
    }

    /**
     * Create a new translation key from dot notation
     */
    public static function createFromDotNotation(string $fullKey, array $attributes = []): self
    {
        $parts = explode('.', $fullKey);
        
        // Handle different key formats
        if (count($parts) === 2) {
            // namespace.key format
            $namespace = $parts[0];
            $group = null;
            $key = $parts[1];
        } elseif (count($parts) === 3) {
            // namespace.group.key format
            $namespace = $parts[0];
            $group = $parts[1];
            $key = $parts[2];
        } else {
            // Single key or complex nesting
            $namespace = 'common';
            $group = null;
            $key = $fullKey;
        }

        return self::create(array_merge([
            'namespace' => $namespace,
            'group' => $group,
            'key' => $key,
        ], $attributes));
    }

    /**
     * Import multiple keys from array structure
     */
    public static function importFromArray(string $namespace, array $data, ?string $parentGroup = null): int
    {
        $imported = 0;

        foreach ($data as $key => $value) {
            $group = $parentGroup;
            
            if (is_array($value)) {
                // Nested structure - use key as group
                $group = $parentGroup ? "{$parentGroup}.{$key}" : $key;
                $imported += self::importFromArray($namespace, $value, $group);
            } else {
                // Create translation key
                $translationKey = self::firstOrCreate([
                    'namespace' => $namespace,
                    'group' => $group,
                    'key' => $key,
                ], [
                    'description' => null,
                    'type' => 'text',
                    'is_construction_term' => $namespace === 'construction',
                    'requires_localization' => true,
                ]);

                $imported++;
            }
        }

        return $imported;
    }
}