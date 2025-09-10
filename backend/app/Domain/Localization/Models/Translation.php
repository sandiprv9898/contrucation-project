<?php

namespace App\Domain\Localization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\User\Models\User;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'translation_key_id',
        'language_id',
        'value',
        'plural_forms',
        'pronunciation',
        'metadata',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'plural_forms' => 'array',
        'metadata' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the translation key
     */
    public function translationKey(): BelongsTo
    {
        return $this->belongsTo(TranslationKey::class);
    }

    /**
     * Get the language
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Get the user who created this translation
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this translation
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope to get only approved translations
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get pending translations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get draft translations
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to filter by language
     */
    public function scopeForLanguage($query, Language $language)
    {
        return $query->where('language_id', $language->id);
    }

    /**
     * Check if translation is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if translation is pending approval
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if translation is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Approve the translation
     */
    public function approve(?User $approver = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver?->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject the translation
     */
    public function reject(?User $rejector = null): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $rejector?->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * Submit for approval
     */
    public function submitForApproval(): void
    {
        $this->update(['status' => 'pending']);
    }

    /**
     * Get plural form for specific count
     */
    public function getPluralForm(int $count): string
    {
        if (!$this->plural_forms || !is_array($this->plural_forms)) {
            return $this->value;
        }

        $language = $this->language;
        
        // Handle different pluralization rules
        switch ($language->code) {
            case 'en':
                return $count === 1 ? 
                    ($this->plural_forms['singular'] ?? $this->value) : 
                    ($this->plural_forms['plural'] ?? $this->value);
            
            case 'ar':
                // Arabic has complex pluralization rules
                if ($count === 0) {
                    return $this->plural_forms['zero'] ?? $this->value;
                } elseif ($count === 1) {
                    return $this->plural_forms['singular'] ?? $this->value;
                } elseif ($count === 2) {
                    return $this->plural_forms['dual'] ?? $this->value;
                } elseif ($count >= 3 && $count <= 10) {
                    return $this->plural_forms['few'] ?? $this->value;
                } elseif ($count >= 11 && $count <= 99) {
                    return $this->plural_forms['many'] ?? $this->value;
                } else {
                    return $this->plural_forms['other'] ?? $this->value;
                }
            
            case 'fr':
            case 'es':
                // French and Spanish: 0 and 1 are singular, others plural
                return ($count === 0 || $count === 1) ? 
                    ($this->plural_forms['singular'] ?? $this->value) : 
                    ($this->plural_forms['plural'] ?? $this->value);
            
            default:
                // Default to English-like pluralization
                return $count === 1 ? 
                    ($this->plural_forms['singular'] ?? $this->value) : 
                    ($this->plural_forms['plural'] ?? $this->value);
        }
    }

    /**
     * Get formatted value with placeholders replaced
     */
    public function getFormattedValue(array $replacements = []): string
    {
        $value = $this->value;

        foreach ($replacements as $key => $replacement) {
            $value = str_replace('{' . $key . '}', $replacement, $value);
        }

        return $value;
    }

    /**
     * Get construction term metadata
     */
    public function getConstructionMetadata(): ?array
    {
        if (!$this->translationKey->is_construction_term) {
            return null;
        }

        return [
            'category' => $this->translationKey->group,
            'pronunciation' => $this->pronunciation,
            'usage_context' => $this->metadata['usage_context'] ?? null,
            'related_terms' => $this->metadata['related_terms'] ?? [],
            'safety_notes' => $this->metadata['safety_notes'] ?? null,
        ];
    }

    /**
     * Create or update translation
     */
    public static function createOrUpdate(
        TranslationKey $translationKey,
        Language $language,
        string $value,
        array $attributes = []
    ): self {
        return self::updateOrCreate([
            'translation_key_id' => $translationKey->id,
            'language_id' => $language->id,
        ], array_merge([
            'value' => $value,
        ], $attributes));
    }

    /**
     * Bulk create translations from array
     */
    public static function bulkCreate(Language $language, array $translations, ?User $creator = null): int
    {
        $created = 0;

        foreach ($translations as $fullKey => $value) {
            $translationKey = TranslationKey::where('namespace', explode('.', $fullKey)[0])
                ->where('key', last(explode('.', $fullKey)))
                ->first();

            if (!$translationKey) {
                // Create the key if it doesn't exist
                $translationKey = TranslationKey::createFromDotNotation($fullKey);
            }

            self::createOrUpdate($translationKey, $language, $value, [
                'status' => 'approved',
                'created_by' => $creator?->id,
                'approved_by' => $creator?->id,
                'approved_at' => now(),
            ]);

            $created++;
        }

        return $created;
    }
}