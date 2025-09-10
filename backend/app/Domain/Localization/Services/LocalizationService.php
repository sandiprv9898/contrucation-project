<?php

namespace App\Domain\Localization\Services;

use App\Domain\Localization\Models\Language;
use App\Domain\Localization\Models\Translation;
use App\Domain\Localization\Models\TranslationKey;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LocalizationService
{
    /**
     * Cache key prefix for translations
     */
    private const CACHE_PREFIX = 'translations:';

    /**
     * Cache TTL in seconds (24 hours)
     */
    private const CACHE_TTL = 86400;

    /**
     * Get all translations for a specific language
     */
    public function getTranslationsForLanguage(Language $language, ?array $namespaces = null): array
    {
        $cacheKey = $this->getCacheKey($language->code, $namespaces);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($language, $namespaces) {
            $query = Translation::approved()
                ->forLanguage($language)
                ->with('translationKey');

            if ($namespaces) {
                $query->whereHas('translationKey', function ($q) use ($namespaces) {
                    $q->whereIn('namespace', $namespaces);
                });
            }

            $translations = $query->get();

            return $this->buildNestedTranslations($translations);
        });
    }

    /**
     * Get specific translation by key
     */
    public function getTranslation(string $key, Language $language, ?string $fallback = null): string
    {
        $translationKey = $this->findTranslationKey($key);

        if (!$translationKey) {
            Log::warning("Translation key not found: {$key}");
            return $fallback ?? $key;
        }

        return $translationKey->getTranslationValue($language, $fallback);
    }

    /**
     * Get translation with pluralization
     */
    public function getTranslationPlural(string $key, int $count, Language $language, array $replacements = []): string
    {
        $translationKey = $this->findTranslationKey($key);

        if (!$translationKey) {
            return $key;
        }

        $translation = $translationKey->getTranslation($language);

        if (!$translation) {
            return $key;
        }

        $pluralForm = $translation->getPluralForm($count);
        $replacements['count'] = $count;

        return $translation->getFormattedValue($replacements);
    }

    /**
     * Get construction terms for a specific language
     */
    public function getConstructionTerms(Language $language, ?string $category = null): array
    {
        $cacheKey = $this->getCacheKey($language->code, ['construction'], $category);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($language, $category) {
            $query = Translation::approved()
                ->forLanguage($language)
                ->whereHas('translationKey', function ($q) use ($category) {
                    $q->where('is_construction_term', true);
                    if ($category) {
                        $q->where('group', $category);
                    }
                })
                ->with('translationKey');

            $translations = $query->get();
            $terms = [];

            foreach ($translations as $translation) {
                $key = $translation->translationKey;
                $category = $key->group ?? 'general';

                if (!isset($terms[$category])) {
                    $terms[$category] = [];
                }

                $terms[$category][$key->key] = [
                    'value' => $translation->value,
                    'pronunciation' => $translation->pronunciation,
                    'metadata' => $translation->getConstructionMetadata(),
                ];
            }

            return $terms;
        });
    }

    /**
     * Search translations by term
     */
    public function searchTranslations(string $searchTerm, Language $language, ?array $namespaces = null): array
    {
        $query = Translation::approved()
            ->forLanguage($language)
            ->where('value', 'ILIKE', "%{$searchTerm}%")
            ->with('translationKey');

        if ($namespaces) {
            $query->whereHas('translationKey', function ($q) use ($namespaces) {
                $q->whereIn('namespace', $namespaces);
            });
        }

        $results = [];
        foreach ($query->get() as $translation) {
            $key = $translation->translationKey;
            $results[] = [
                'key' => $key->getFullKey(),
                'namespace' => $key->namespace,
                'group' => $key->group,
                'original_key' => $key->key,
                'value' => $translation->value,
                'is_construction_term' => $key->is_construction_term,
            ];
        }

        return $results;
    }

    /**
     * Create or update translation
     */
    public function saveTranslation(
        string $key,
        Language $language,
        string $value,
        ?User $user = null,
        array $options = []
    ): Translation {
        $translationKey = $this->findOrCreateTranslationKey($key, $options);

        $translation = Translation::createOrUpdate($translationKey, $language, $value, [
            'status' => $options['status'] ?? 'draft',
            'pronunciation' => $options['pronunciation'] ?? null,
            'metadata' => $options['metadata'] ?? null,
            'created_by' => $user?->id,
        ]);

        // Clear cache for this language
        $this->clearLanguageCache($language);

        return $translation;
    }

    /**
     * Bulk import translations from array
     */
    public function importTranslations(Language $language, array $translations, ?User $user = null): int
    {
        $imported = Translation::bulkCreate($language, $translations, $user);

        // Clear cache for this language
        $this->clearLanguageCache($language);

        return $imported;
    }

    /**
     * Get language statistics
     */
    public function getLanguageStats(Language $language): array
    {
        $totalKeys = TranslationKey::requiresLocalization()->count();
        $translatedKeys = Translation::approved()
            ->forLanguage($language)
            ->whereHas('translationKey', function ($q) {
                $q->where('requires_localization', true);
            })
            ->count();

        $constructionTerms = Translation::approved()
            ->forLanguage($language)
            ->whereHas('translationKey', function ($q) {
                $q->where('is_construction_term', true);
            })
            ->count();

        return [
            'total_keys' => $totalKeys,
            'translated_keys' => $translatedKeys,
            'construction_terms' => $constructionTerms,
            'completion_percentage' => $totalKeys > 0 ? round(($translatedKeys / $totalKeys) * 100, 2) : 0,
            'missing_keys' => $totalKeys - $translatedKeys,
        ];
    }

    /**
     * Get all active languages with their completion stats
     */
    public function getActiveLanguagesWithStats(): array
    {
        $languages = Language::active()->ordered()->get();
        $stats = [];

        foreach ($languages as $language) {
            $stats[] = [
                'language' => [
                    'id' => $language->id,
                    'code' => $language->code,
                    'name' => $language->name,
                    'native_name' => $language->native_name,
                    'flag_emoji' => $language->flag_emoji,
                    'direction' => $language->direction,
                    'is_default' => $language->is_default,
                ],
                'stats' => $this->getLanguageStats($language),
            ];
        }

        return $stats;
    }

    /**
     * Get user's preferred language or default
     */
    public function getUserLanguage(?User $user = null): Language
    {
        if ($user && $user->preferredLanguage) {
            return $user->preferredLanguage;
        }

        return Language::getDefault();
    }

    /**
     * Set user's preferred language
     */
    public function setUserLanguage(User $user, Language $language): void
    {
        $user->update(['preferred_language_id' => $language->id]);
    }

    /**
     * Clear all translation caches
     */
    public function clearAllCaches(): void
    {
        $languages = Language::active()->get();

        foreach ($languages as $language) {
            $this->clearLanguageCache($language);
        }

        Cache::tags(['translations'])->flush();
    }

    /**
     * Clear cache for specific language
     */
    public function clearLanguageCache(Language $language): void
    {
        $patterns = [
            $this->getCacheKey($language->code),
            $this->getCacheKey($language->code, ['construction']),
            $this->getCacheKey($language->code, ['common']),
            $this->getCacheKey($language->code, ['navigation']),
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Build nested translation array from flat translations
     */
    private function buildNestedTranslations($translations): array
    {
        $nested = [];

        foreach ($translations as $translation) {
            $key = $translation->translationKey;
            $namespace = $key->namespace;
            $group = $key->group;
            $keyName = $key->key;

            if (!isset($nested[$namespace])) {
                $nested[$namespace] = [];
            }

            if ($group) {
                if (!isset($nested[$namespace][$group])) {
                    $nested[$namespace][$group] = [];
                }
                $nested[$namespace][$group][$keyName] = $translation->value;
            } else {
                $nested[$namespace][$keyName] = $translation->value;
            }
        }

        return $nested;
    }

    /**
     * Find translation key by dot notation
     */
    private function findTranslationKey(string $key): ?TranslationKey
    {
        $parts = explode('.', $key);

        if (count($parts) === 2) {
            return TranslationKey::where('namespace', $parts[0])
                ->whereNull('group')
                ->where('key', $parts[1])
                ->first();
        } elseif (count($parts) === 3) {
            return TranslationKey::where('namespace', $parts[0])
                ->where('group', $parts[1])
                ->where('key', $parts[2])
                ->first();
        }

        return null;
    }

    /**
     * Find or create translation key
     */
    private function findOrCreateTranslationKey(string $key, array $options = []): TranslationKey
    {
        $translationKey = $this->findTranslationKey($key);

        if (!$translationKey) {
            $translationKey = TranslationKey::createFromDotNotation($key, [
                'description' => $options['description'] ?? null,
                'is_construction_term' => $options['is_construction_term'] ?? false,
                'requires_localization' => $options['requires_localization'] ?? true,
            ]);
        }

        return $translationKey;
    }

    /**
     * Generate cache key
     */
    private function getCacheKey(string $languageCode, ?array $namespaces = null, ?string $category = null): string
    {
        $key = self::CACHE_PREFIX . $languageCode;

        if ($namespaces) {
            $key .= ':' . implode(',', $namespaces);
        }

        if ($category) {
            $key .= ':' . $category;
        }

        return $key;
    }
}