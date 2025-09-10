<?php

namespace App\Http\Requests\CompanyBranding;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandingAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_variant' => 'nullable|string|in:light,dark,color,mono,transparent',
            'is_active' => 'boolean',
            'set_as_active' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'asset_variant.in' => 'Invalid asset variant selected.',
        ];
    }
}