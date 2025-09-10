<?php

namespace App\Http\Requests\CompanyProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_registration' => 'nullable|string|max:100',
            'tax_identification' => 'nullable|string|max:100',
            'industry_type' => 'nullable|string|max:50',
            'company_size' => 'nullable|in:startup,small,medium,large,enterprise',
            'founded_date' => 'nullable|date|before:today',
            'description' => 'nullable|string|max:65535',
            'website' => 'nullable|url|max:255',
            'social_media' => 'nullable|array',
            'social_media.facebook' => 'nullable|url',
            'social_media.twitter' => 'nullable|url',
            'social_media.linkedin' => 'nullable|url',
            'social_media.instagram' => 'nullable|url',
            'social_media.youtube' => 'nullable|url',
            'certifications' => 'nullable|array',
            'certifications.*.name' => 'required_with:certifications|string|max:255',
            'certifications.*.issuer' => 'nullable|string|max:255',
            'certifications.*.date_issued' => 'nullable|date',
            'certifications.*.expiry_date' => 'nullable|date|after:date_issued',
            'certifications.*.certificate_number' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'founded_date.before' => 'The founded date must be before today.',
            'website.url' => 'Please provide a valid website URL.',
            'social_media.*.url' => 'Please provide valid social media URLs.',
            'certifications.*.expiry_date.after' => 'The expiry date must be after the issue date.',
        ];
    }
}