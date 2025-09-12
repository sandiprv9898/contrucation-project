<?php

namespace App\Http\Requests\Gantt;

use Illuminate\Foundation\Http\FormRequest;

class GanttFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|array',
            'status.*' => 'in:pending,in_progress,completed,on_hold,cancelled',
            'assignees' => 'sometimes|array',
            'assignees.*' => 'string|exists:users,id',
            'phases' => 'sometimes|array',
            'phases.*' => 'string|exists:project_phases,id',
            'priority' => 'sometimes|array',
            'priority.*' => 'in:low,medium,high,critical',
            'tags' => 'sometimes|array',
            'tags.*' => 'string',
            'date_range' => 'sometimes|array',
            'date_range.start' => 'sometimes|date',
            'date_range.end' => 'sometimes|date|after:date_range.start',
            'search' => 'sometimes|string|max:255',
            'show_completed' => 'sometimes|boolean',
            'show_overdue' => 'sometimes|boolean',
            'group_by' => 'sometimes|in:phase,assignee,status,priority',
        ];
    }
}