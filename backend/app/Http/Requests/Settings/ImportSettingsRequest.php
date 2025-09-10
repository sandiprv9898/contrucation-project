<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class ImportSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole(['admin']);
    }

    public function rules(): array
    {
        return [
            'settings' => 'required|array|min:1',
            'settings.*.category' => 'required|string|in:company,system,notifications,security,backup',
            'settings.*.key' => 'required|string|max:100',
            'settings.*.value' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'settings.required' => 'Settings data is required for import.',
            'settings.array' => 'Settings must be an array.',
            'settings.min' => 'At least one setting is required for import.',
            'settings.*.category.required' => 'Each setting must have a category.',
            'settings.*.category.in' => 'Invalid category. Must be one of: company, system, notifications, security, backup.',
            'settings.*.key.required' => 'Each setting must have a key.',
            'settings.*.value.required' => 'Each setting must have a value.',
        ];
    }
}