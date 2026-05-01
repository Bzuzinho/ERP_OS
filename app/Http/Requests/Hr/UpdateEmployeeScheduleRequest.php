<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('manage_schedules', 'hr') ?? false;
    }

    public function rules(): array
    {
        return [
            'weekday' => ['nullable', 'integer', 'min:0', 'max:6'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'break_minutes' => ['integer', 'min:0'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
