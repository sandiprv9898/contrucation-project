<?php

namespace App\Http\Requests\Gantt;

use Illuminate\Foundation\Http\FormRequest;

class AutoScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'respect_dependencies' => 'sometimes|boolean',
            'optimize_resources' => 'sometimes|boolean',
            'avoid_weekends' => 'sometimes|boolean',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'working_hours_per_day' => 'sometimes|integer|min:1|max:24',
            'buffer_days' => 'sometimes|integer|min:0|max:30',
        ];
    }
}