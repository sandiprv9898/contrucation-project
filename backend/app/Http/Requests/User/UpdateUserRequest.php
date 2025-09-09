<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');
        $currentUser = auth()->user();

        // Admin can edit anyone
        if ($currentUser->isAdmin()) {
            return true;
        }

        // Users can edit their own profile (limited fields)
        return $currentUser->id === $user->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $currentUser = auth()->user();

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
        ];

        // Only admins can change these fields
        if ($currentUser->isAdmin()) {
            $rules['role'] = 'sometimes|required|string|in:admin,project_manager,supervisor,field_worker';
            $rules['company_id'] = 'sometimes|nullable|exists:companies,id';
        }

        // Password update (optional)
        if ($this->has('password')) {
            $rules['password'] = 'sometimes|string|min:8|confirmed';
        }

        // Avatar URL
        $rules['avatar_url'] = 'sometimes|nullable|string|max:255';

        return $rules;
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.unique' => 'This email address is already taken.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'role.in' => 'The selected role is invalid.',
        ];
    }
}