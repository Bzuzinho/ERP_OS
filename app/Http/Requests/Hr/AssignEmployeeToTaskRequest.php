<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class AssignEmployeeToTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('create', \App\Models\EmployeeTaskAssignment::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'task_id' => ['required', 'exists:tasks,id'],
            'role' => ['nullable', 'string', 'max:255'],
        ];
    }
}
