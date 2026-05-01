<?php

namespace App\Http\Requests\Planning;

use App\Models\RecurringOperation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecurringOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('planning.manage_recurring');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'operation_type' => ['required', Rule::in(RecurringOperation::TYPES)],
            'status' => ['nullable', Rule::in(RecurringOperation::STATUSES)],
            'frequency' => ['required', Rule::in(RecurringOperation::FREQUENCIES)],
            'interval' => ['required', 'integer', 'min:1'],
            'weekdays' => ['nullable', 'array'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'related_space_id' => ['nullable', 'exists:spaces,id'],
            'task_template' => ['nullable', 'array'],
            'task_template.title' => ['required_if:operation_type,task', 'nullable', 'string', 'max:255'],
            'event_template' => ['nullable', 'array'],
            'event_template.title' => ['required_if:operation_type,event', 'nullable', 'string', 'max:255'],
            'event_template.start_at' => ['required_if:operation_type,event', 'nullable', 'date'],
            'event_template.end_at' => ['required_if:operation_type,event', 'nullable', 'date', 'after:event_template.start_at'],
        ];
    }
}
