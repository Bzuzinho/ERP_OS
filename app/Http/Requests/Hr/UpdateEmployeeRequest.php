<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('update', $this->employee) ?? false;
    }

    public function rules(): array
    {
        $employeeId = $this->employee->id;

        return [
            'user_id' => ['nullable', 'exists:users,id', "unique:employees,user_id,{$employeeId}"],
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_number' => ['nullable', 'string', 'max:255'],
            'role_title' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', 'string', 'in:permanent,contract,temporary,volunteer,external,other'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
