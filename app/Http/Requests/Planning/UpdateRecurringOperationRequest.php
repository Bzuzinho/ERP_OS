<?php

namespace App\Http\Requests\Planning;

use App\Models\RecurringOperation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecurringOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('planning.manage_recurring');
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'operation_type' => ['sometimes', Rule::in(RecurringOperation::TYPES)],
            'status' => ['nullable', Rule::in(RecurringOperation::STATUSES)],
            'frequency' => ['sometimes', Rule::in(RecurringOperation::FREQUENCIES)],
            'interval' => ['sometimes', 'integer', 'min:1'],
            'weekdays' => ['nullable', 'array'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'related_space_id' => ['nullable', 'exists:spaces,id'],
            'task_template' => ['nullable', 'array'],
            'event_template' => ['nullable', 'array'],
        ];
    }
}
