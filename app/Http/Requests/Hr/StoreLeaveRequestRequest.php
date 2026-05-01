<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('create', \App\Models\LeaveRequest::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'absence_type_id' => ['nullable', 'exists:absence_types,id'],
            'leave_type' => ['required', 'string', 'in:vacation,sick_leave,justified_absence,unpaid_leave,family_support,training,other'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
