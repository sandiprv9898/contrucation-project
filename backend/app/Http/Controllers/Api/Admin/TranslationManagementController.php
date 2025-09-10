<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Localization\Models\Language;
use App\Domain\Localization\Models\Translation;
use App\Domain\Localization\Models\TranslationKey;
use App\Domain\Localization\Services\LocalizationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TranslationManagementController extends Controller
{
    private LocalizationService $localizationService;

    public function __construct(LocalizationService $localizationService)
    {
        $this->localizationService = $localizationService;
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Get all translation keys with completion status
     */
    public function keys(Request $request): JsonResponse
    {
        $request->validate([
            'namespace' => 'nullable|string',
            'group' => 'nullable|string',
            'construction_terms_only' => 'nullable|boolean',
            'missing_only' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = TranslationKey::with(['translations.language'])
            ->when($request->namespace, fn($q) => $q->where('namespace', $request->namespace))
            ->when($request->group, fn($q) => $q->where('group', $request->group))
            ->when($request->construction_terms_only, fn($q) => $q->where('is_construction_term', true))
            ->orderBy('namespace')
            ->orderBy('group')
            ->orderBy('key');

        $keys = $query->paginate($request->get('per_page', 15));

        $keysData = $keys->getCollection()->map(function ($key) {
            return [
                'id' => $key->id,
                'full_key' => $key->getFullKey(),
                'namespace' => $key->namespace,
                'group' => $key->group,
                'key' => $key->key,
                'description' => $key->description,
                'is_construction_term' => $key->is_construction_term,
                'requires_localization' => $key->requires_localization,
                'completion_status' => $key->getCompletionStatus(),
                'created_at' => $key->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $keysData,
            'meta' => [
                'pagination' => [
                    'current_page' => $keys->currentPage(),
                    'last_page' => $keys->lastPage(),
                    'per_page' => $keys->perPage(),
                    'total' => $keys->total(),
                ],
            ]
        ]);
    }

    /**
     * Create a new translation key
     */
    public function createKey(Request $request): JsonResponse
    {
        $request->validate([
            'full_key' => 'required|string|unique:translation_keys,id',
            'description' => 'nullable|string|max:500',
            'is_construction_term' => 'boolean',
            'requires_localization' => 'boolean',
        ]);

        $key = TranslationKey::createFromDotNotation($request->full_key, [
            'description' => $request->description,
            'is_construction_term' => $request->get('is_construction_term', false),
            'requires_localization' => $request->get('requires_localization', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Translation key created successfully',
            'data' => [
                'id' => $key->id,
                'full_key' => $key->getFullKey(),
                'namespace' => $key->namespace,
                'group' => $key->group,
                'key' => $key->key,
            ]
        ], 201);
    }

    /**
     * Get translations for a specific key
     */
    public function keyTranslations(int $keyId): JsonResponse
    {
        $key = TranslationKey::with(['translations.language', 'translations.creator', 'translations.approver'])
            ->findOrFail($keyId);

        $translations = $key->translations->map(function ($translation) {
            return [
                'id' => $translation->id,
                'language' => [
                    'code' => $translation->language->code,
                    'name' => $translation->language->name,
                    'native_name' => $translation->language->native_name,
                    'flag_emoji' => $translation->language->flag_emoji,
                ],
                'value' => $translation->value,
                'plural_forms' => $translation->plural_forms,
                'pronunciation' => $translation->pronunciation,
                'status' => $translation->status,
                'metadata' => $translation->metadata,
                'creator' => $translation->creator ? [
                    'name' => $translation->creator->name,
                    'email' => $translation->creator->email,
                ] : null,
                'approver' => $translation->approver ? [
                    'name' => $translation->approver->name,
                    'email' => $translation->approver->email,
                ] : null,
                'created_at' => $translation->created_at->toISOString(),
                'approved_at' => $translation->approved_at?->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'key' => [
                    'id' => $key->id,
                    'full_key' => $key->getFullKey(),
                    'description' => $key->description,
                    'is_construction_term' => $key->is_construction_term,
                ],
                'translations' => $translations,
            ]
        ]);
    }

    /**
     * Create or update translation
     */
    public function saveTranslation(Request $request): JsonResponse
    {
        $request->validate([
            'key_id' => 'required|integer|exists:translation_keys,id',
            'language_code' => 'required|string|exists:languages,code',
            'value' => 'required|string|max:2000',
            'plural_forms' => 'nullable|array',
            'pronunciation' => 'nullable|string|max:200',
            'metadata' => 'nullable|array',
            'status' => ['nullable', Rule::in(['draft', 'pending', 'approved'])],
        ]);

        $key = TranslationKey::findOrFail($request->key_id);
        $language = Language::where('code', $request->language_code)->firstOrFail();

        $translation = $this->localizationService->saveTranslation(
            $key->getFullKey(),
            $language,
            $request->value,
            $request->user(),
            [
                'plural_forms' => $request->plural_forms,
                'pronunciation' => $request->pronunciation,
                'metadata' => $request->metadata,
                'status' => $request->get('status', 'draft'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Translation saved successfully',
            'data' => [
                'id' => $translation->id,
                'value' => $translation->value,
                'status' => $translation->status,
                'language' => $language->code,
            ]
        ], $translation->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Approve translation
     */
    public function approveTranslation(int $translationId): JsonResponse
    {
        $translation = Translation::findOrFail($translationId);
        $translation->approve(auth()->user());

        $this->localizationService->clearLanguageCache($translation->language);

        return response()->json([
            'success' => true,
            'message' => 'Translation approved successfully',
            'data' => [
                'id' => $translation->id,
                'status' => $translation->status,
                'approved_at' => $translation->approved_at->toISOString(),
            ]
        ]);
    }

    /**
     * Reject translation
     */
    public function rejectTranslation(int $translationId): JsonResponse
    {
        $translation = Translation::findOrFail($translationId);
        $translation->reject(auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Translation rejected',
            'data' => [
                'id' => $translation->id,
                'status' => $translation->status,
            ]
        ]);
    }

    /**
     * Bulk import translations
     */
    public function bulkImport(Request $request): JsonResponse
    {
        $request->validate([
            'language_code' => 'required|string|exists:languages,code',
            'translations' => 'required|array|min:1',
            'translations.*' => 'required|string',
            'namespace' => 'nullable|string',
            'auto_approve' => 'boolean',
        ]);

        $language = Language::where('code', $request->language_code)->firstOrFail();

        DB::beginTransaction();

        try {
            $imported = $this->localizationService->importTranslations(
                $language,
                $request->translations,
                $request->user()
            );

            if ($request->get('auto_approve', false)) {
                Translation::where('language_id', $language->id)
                    ->where('status', 'draft')
                    ->whereHas('translationKey', function ($query) use ($request) {
                        if ($request->namespace) {
                            $query->where('namespace', $request->namespace);
                        }
                    })
                    ->update([
                        'status' => 'approved',
                        'approved_by' => $request->user()->id,
                        'approved_at' => now(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} translations",
                'data' => [
                    'imported_count' => $imported,
                    'language' => $language->code,
                    'auto_approved' => $request->get('auto_approve', false),
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending translations for review
     */
    public function pendingTranslations(Request $request): JsonResponse
    {
        $request->validate([
            'language_code' => 'nullable|string|exists:languages,code',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Translation::with(['translationKey', 'language', 'creator'])
            ->where('status', 'pending')
            ->when($request->language_code, fn($q) => $q->whereHas('language', fn($lang) => $lang->where('code', $request->language_code)))
            ->latest();

        $translations = $query->paginate($request->get('per_page', 20));

        $data = $translations->getCollection()->map(function ($translation) {
            return [
                'id' => $translation->id,
                'key' => $translation->translationKey->getFullKey(),
                'value' => $translation->value,
                'language' => [
                    'code' => $translation->language->code,
                    'name' => $translation->language->name,
                    'flag_emoji' => $translation->language->flag_emoji,
                ],
                'creator' => $translation->creator ? [
                    'name' => $translation->creator->name,
                    'email' => $translation->creator->email,
                ] : null,
                'created_at' => $translation->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'pagination' => [
                    'current_page' => $translations->currentPage(),
                    'last_page' => $translations->lastPage(),
                    'per_page' => $translations->perPage(),
                    'total' => $translations->total(),
                ],
            ]
        ]);
    }

    /**
     * Clear translation cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        $request->validate([
            'language_code' => 'nullable|string|exists:languages,code',
        ]);

        if ($request->language_code) {
            $language = Language::where('code', $request->language_code)->firstOrFail();
            $this->localizationService->clearLanguageCache($language);
            $message = "Cache cleared for {$language->name}";
        } else {
            $this->localizationService->clearAllCaches();
            $message = 'All translation caches cleared';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}