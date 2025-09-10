<?php

namespace App\Http\Requests\CompanyPortfolio;

use Illuminate\Foundation\Http\FormRequest;

class StorePortfolioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:65535',
            'category' => 'nullable|string|in:project,certification,award,team,testimonial,case_study',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB
            'external_url' => 'nullable|url|max:500',
            'display_order' => 'nullable|integer|min:0',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for the portfolio item.',
            'title.max' => 'The title must not exceed 255 characters.',
            'description.max' => 'The description is too long.',
            'category.in' => 'Invalid category selected.',
            'image.mimes' => 'The image must be JPEG, PNG, GIF, or WebP format.',
            'image.max' => 'The image size must not exceed 5MB.',
            'external_url.url' => 'Please provide a valid URL.',
            'display_order.min' => 'Display order must be a positive number.',
        ];
    }
}