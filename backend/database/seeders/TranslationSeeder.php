<?php

namespace Database\Seeders;

use App\Domain\Localization\Models\Language;
use App\Domain\Localization\Models\TranslationKey;
use App\Domain\Localization\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting translation import...');

        // Define frontend translation files path
        $frontendPath = base_path('../frontend/src/locales');
        
        if (!File::exists($frontendPath)) {
            $this->command->warn("Frontend locales directory not found at: {$frontendPath}");
            return;
        }

        $languages = Language::active()->get()->keyBy('code');
        $imported = 0;

        foreach ($languages as $language) {
            $this->command->info("Importing translations for {$language->name} ({$language->code})...");
            
            $langDir = "{$frontendPath}/{$language->code}";
            
            if (!File::exists($langDir)) {
                $this->command->warn("Language directory not found: {$langDir}");
                continue;
            }

            // Import construction terms
            $constructionFile = "{$langDir}/construction.json";
            if (File::exists($constructionFile)) {
                $constructionTerms = json_decode(File::get($constructionFile), true);
                if ($constructionTerms) {
                    $imported += $this->importTranslations(
                        $language,
                        'construction',
                        $constructionTerms,
                        true // is_construction_term
                    );
                }
            }

            // Import common translations (if they exist)
            $commonFile = "{$langDir}/common.json";
            if (File::exists($commonFile)) {
                $commonTerms = json_decode(File::get($commonFile), true);
                if ($commonTerms) {
                    $imported += $this->importTranslations(
                        $language,
                        'common',
                        $commonTerms,
                        false // is_construction_term
                    );
                }
            }

            // Import navigation translations (if they exist)
            $navigationFile = "{$langDir}/navigation.json";
            if (File::exists($navigationFile)) {
                $navTerms = json_decode(File::get($navigationFile), true);
                if ($navTerms) {
                    $imported += $this->importTranslations(
                        $language,
                        'navigation',
                        $navTerms,
                        false // is_construction_term
                    );
                }
            }
        }

        $this->command->info("Translation import completed. Total translations imported: {$imported}");
    }

    /**
     * Import translations for a specific namespace
     */
    private function importTranslations(Language $language, string $namespace, array $translations, bool $isConstructionTerm = false): int
    {
        $imported = 0;

        foreach ($translations as $group => $items) {
            if (!is_array($items)) {
                // Handle flat structure (no groups)
                $key = $this->createTranslationKey($namespace, null, $group, $isConstructionTerm);
                $this->createTranslation($key, $language, $items);
                $imported++;
                continue;
            }

            foreach ($items as $itemKey => $value) {
                if (is_array($value)) {
                    // Handle nested structure (terms with metadata)
                    $key = $this->createTranslationKey($namespace, $group, $itemKey, $isConstructionTerm);
                    
                    if (isset($value['term'])) {
                        $this->createTranslation($key, $language, $value['term'], [
                            'pronunciation' => $value['pronunciation'] ?? null,
                            'usage_context' => $value['usage_context'] ?? null,
                            'related_terms' => $value['related_terms'] ?? [],
                            'safety_notes' => $value['safety_notes'] ?? null,
                        ]);
                    } else {
                        // Handle regular array values (convert to JSON string)
                        $this->createTranslation($key, $language, json_encode($value));
                    }
                } else {
                    // Handle simple key-value structure
                    $key = $this->createTranslationKey($namespace, $group, $itemKey, $isConstructionTerm);
                    $this->createTranslation($key, $language, $value);
                }
                $imported++;
            }
        }

        return $imported;
    }

    /**
     * Create or get translation key
     */
    private function createTranslationKey(string $namespace, ?string $group, string $key, bool $isConstructionTerm = false): TranslationKey
    {
        return TranslationKey::firstOrCreate([
            'namespace' => $namespace,
            'group' => $group,
            'key' => $key,
        ], [
            'description' => $isConstructionTerm ? "Construction industry term" : null,
            'is_construction_term' => $isConstructionTerm,
            'requires_localization' => true,
            'type' => 'text',
        ]);
    }

    /**
     * Create translation
     */
    private function createTranslation(TranslationKey $translationKey, Language $language, string $value, array $metadata = []): Translation
    {
        return Translation::updateOrCreate([
            'translation_key_id' => $translationKey->id,
            'language_id' => $language->id,
        ], [
            'value' => $value,
            'status' => 'approved', // Auto-approve imported translations
            'metadata' => empty($metadata) ? null : $metadata,
            'approved_at' => now(),
        ]);
    }
}