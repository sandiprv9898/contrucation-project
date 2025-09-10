<?php

namespace App\Http\Requests\CompanyBranding;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandingAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_type' => 'required|string|in:logo,favicon,banner,watermark,icon',
            'asset_variant' => 'nullable|string|in:light,dark,color,mono,transparent',
            'file' => 'required|file|mimes:jpeg,jpg,png,svg,gif,webp|max:10240', // 10MB
            'set_as_active' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.mimes' => 'The file must be an image (JPEG, PNG, SVG, GIF, or WebP).',
            'file.max' => 'The file size must not exceed 10MB.',
            'asset_type.required' => 'Please specify the asset type.',
            'asset_type.in' => 'Invalid asset type selected.',
        ];
    }
}