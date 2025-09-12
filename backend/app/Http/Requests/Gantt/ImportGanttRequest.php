<?php

namespace App\Http\Requests\Gantt;

use Illuminate\Foundation\Http\FormRequest;

class ImportGanttRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:mpp,xml,csv,xlsx|max:10240', // 10MB max
            'format' => 'required|in:mpp,xml,csv',
            'overwrite_existing' => 'sometimes|boolean',
            'create_dependencies' => 'sometimes|boolean',
            'update_project_dates' => 'sometimes|boolean',
        ];
    }
}