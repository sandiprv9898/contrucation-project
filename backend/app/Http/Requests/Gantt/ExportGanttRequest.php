<?php

namespace App\Http\Requests\Gantt;

use Illuminate\Foundation\Http\FormRequest;

class ExportGanttRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'format' => 'required|in:pdf,excel,png,mpp',
            'include_details' => 'sometimes|boolean',
            'include_dependencies' => 'sometimes|boolean',
            'include_resources' => 'sometimes|boolean',
            'date_range' => 'sometimes|array',
            'date_range.start' => 'sometimes|date',
            'date_range.end' => 'sometimes|date|after:date_range.start',
            'paper_size' => 'sometimes|in:A4,A3,letter,legal',
            'orientation' => 'sometimes|in:portrait,landscape',
        ];
    }
}