<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Localization\Models\Language;
use App\Domain\Localization\Models\TranslationKey;
use App\Domain\Localization\Services\LocalizationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LocalizationController extends Controller
{
    private LocalizationService $localizationService;

    public function __construct(LocalizationService $localizationService)
    {
        $this->localizationService = $localizationService;
    }

    /**
     * Get all active languages with their completion stats
     */
    public function languages(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->localizationService->getActiveLanguagesWithStats()
        ]);
    }

    /**
     * Get translations for a specific language
     */
    public function translations(Request $request, string $languageCode): JsonResponse
    {
        $language = Language::where('code', $languageCode)->firstOrFail();
        
        $namespaces = $request->get('namespaces');
        if ($namespaces && is_string($namespaces)) {
            $namespaces = explode(',', $namespaces);
        }

        $translations = $this->localizationService->getTranslationsForLanguage(
            $language,
            $namespaces
        );

        return response()->json([
            'success' => true,
            'data' => [
                'language' => [
                    'code' => $language->code,
                    'name' => $language->name,
                    'native_name' => $language->native_name,
                    'direction' => $language->direction,
                    'flag_emoji' => $language->flag_emoji,
                ],
                'translations' => $translations,
                'meta' => [
                    'namespaces' => $namespaces,
                    'cached_at' => now()->toISOString(),
                ]
            ]
        ]);
    }

    /**
     * Get construction terms for a specific language
     */
    public function constructionTerms(Request $request, string $languageCode): JsonResponse
    {
        $language = Language::where('code', $languageCode)->firstOrFail();
        $category = $request->get('category');

        $terms = $this->localizationService->getConstructionTerms($language, $category);

        return response()->json([
            'success' => true,
            'data' => [
                'language' => $language->code,
                'category' => $category,
                'terms' => $terms,
                'meta' => [
                    'cached_at' => now()->toISOString(),
                    'total_categories' => count($terms),
                ]
            ]
        ]);
    }

    /**
     * Search translations across languages
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'language' => 'required|string|exists:languages,code',
            'namespaces' => 'nullable|string',
        ]);

        $language = Language::where('code', $request->language)->firstOrFail();
        $namespaces = $request->namespaces ? explode(',', $request->namespaces) : null;

        $results = $this->localizationService->searchTranslations(
            $request->q,
            $language,
            $namespaces
        );

        return response()->json([
            'success' => true,
            'data' => [
                'query' => $request->q,
                'language' => $request->language,
                'results' => $results,
                'meta' => [
                    'total_results' => count($results),
                    'searched_namespaces' => $namespaces,
                ]
            ]
        ]);
    }

    /**
     * Get user's language preferences
     */
    public function userLanguage(Request $request): JsonResponse
    {
        $user = $request->user();
        $language = $this->localizationService->getUserLanguage($user);

        return response()->json([
            'success' => true,
            'data' => [
                'current_language' => [
                    'code' => $language->code,
                    'name' => $language->name,
                    'native_name' => $language->native_name,
                    'direction' => $language->direction,
                    'flag_emoji' => $language->flag_emoji,
                    'date_format' => $language->getJsDateFormat(),
                    'number_format' => $language->getNumberFormat(),
                    'currency_format' => $language->getCurrencyFormat(),
                ],
                'is_default' => !$user || !$user->preferredLanguage,
            ]
        ]);
    }

    /**
     * Update user's language preference
     */
    public function updateUserLanguage(Request $request): JsonResponse
    {
        $request->validate([
            'language_code' => 'required|string|exists:languages,code',
        ]);

        $user = $request->user();
        $language = Language::where('code', $request->language_code)->firstOrFail();

        if (!$language->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Selected language is not active',
            ], 400);
        }

        $this->localizationService->setUserLanguage($user, $language);

        return response()->json([
            'success' => true,
            'message' => 'Language preference updated successfully',
            'data' => [
                'language' => [
                    'code' => $language->code,
                    'name' => $language->name,
                    'native_name' => $language->native_name,
                ]
            ]
        ]);
    }

    /**
     * Get specific translation by key
     */
    public function translation(Request $request, string $languageCode, string $key): JsonResponse
    {
        $language = Language::where('code', $languageCode)->firstOrFail();
        $fallback = $request->get('fallback');

        $translation = $this->localizationService->getTranslation($key, $language, $fallback);

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $key,
                'language' => $languageCode,
                'translation' => $translation,
                'is_fallback' => $translation === $fallback || $translation === $key,
            ]
        ]);
    }

    /**
     * Get language statistics
     */
    public function languageStats(string $languageCode): JsonResponse
    {
        $language = Language::where('code', $languageCode)->firstOrFail();
        $stats = $this->localizationService->getLanguageStats($language);

        return response()->json([
            'success' => true,
            'data' => [
                'language' => [
                    'code' => $language->code,
                    'name' => $language->name,
                ],
                'stats' => $stats,
            ]
        ]);
    }
}