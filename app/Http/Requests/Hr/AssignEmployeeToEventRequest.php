<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class AssignEmployeeToEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('create', \App\Models\EmployeeEventAssignment::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'event_id' => ['required', 'exists:events,id'],
            'role' => ['nullable', 'string', 'max:255'],
        ];
    }
}
