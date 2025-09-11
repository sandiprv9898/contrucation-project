<?php

namespace App\Http\Requests\Project;

use App\Domain\Project\Enums\ProjectPriority;
use App\Domain\Project\Enums\ProjectType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole(['admin', 'project_manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'client_company_id' => ['required', 'uuid', 'exists:companies,id'],
            'project_manager_id' => ['required', 'uuid', 'exists:users,id'],
            'project_type' => ['required', 'string', Rule::enum(ProjectType::class)],
            'priority' => ['required', 'string', Rule::enum(ProjectPriority::class)],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'planned_budget' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'address' => ['nullable', 'string', 'max:1000'],
            'coordinates' => ['nullable', 'array'],
            'coordinates.lat' => ['required_with:coordinates', 'numeric', 'between:-90,90'],
            'coordinates.lng' => ['required_with:coordinates', 'numeric', 'between:-180,180'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Project name is required.',
            'client_company_id.required' => 'Client company is required.',
            'client_company_id.exists' => 'Selected client company does not exist.',
            'project_manager_id.required' => 'Project manager is required.',
            'project_manager_id.exists' => 'Selected project manager does not exist.',
            'end_date.after' => 'End date must be after the start date.',
            'planned_budget.min' => 'Budget cannot be negative.',
            'coordinates.lat.between' => 'Latitude must be between -90 and 90 degrees.',
            'coordinates.lng.between' => 'Longitude must be between -180 and 180 degrees.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'client_company_id' => 'client company',
            'project_manager_id' => 'project manager',
            'project_type' => 'project type',
            'planned_budget' => 'budget',
            'coordinates.lat' => 'latitude',
            'coordinates.lng' => 'longitude',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Transform coordinates if provided
        if ($this->has('coordinates') && is_array($this->coordinates)) {
            $coordinates = $this->coordinates;
            
            // Ensure we have both lat and lng
            if (isset($coordinates['lat']) && isset($coordinates['lng'])) {
                $this->merge([
                    'coordinates' => [
                        'lat' => (float) $coordinates['lat'],
                        'lng' => (float) $coordinates['lng']
                    ]
                ]);
            }
        }

        // Clean up budget field
        if ($this->has('planned_budget') && $this->planned_budget !== null) {
            $this->merge([
                'planned_budget' => (float) $this->planned_budget
            ]);
        }
    }
}