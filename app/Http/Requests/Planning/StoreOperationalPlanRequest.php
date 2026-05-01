<?php

namespace App\Http\Requests\Planning;

use App\Models\OperationalPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOperationalPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', OperationalPlan::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'plan_type' => ['required', Rule::in(OperationalPlan::TYPES)],
            'status' => ['nullable', Rule::in(OperationalPlan::STATUSES)],
            'visibility' => ['required', Rule::in(OperationalPlan::VISIBILITIES)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'related_ticket_id' => ['nullable', 'exists:tickets,id'],
            'related_space_id' => ['nullable', 'exists:spaces,id'],
            'budget_estimate' => ['nullable', 'numeric', 'min:0'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'participants' => ['nullable', 'array'],
            'participants.*.user_id' => ['nullable', 'exists:users,id'],
            'participants.*.employee_id' => ['nullable', 'exists:employees,id'],
            'participants.*.team_id' => ['nullable', 'exists:teams,id'],
            'participants.*.role' => ['nullable', 'string', 'max:255'],
            'resources' => ['nullable', 'array'],
            'resources.*.inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'resources.*.space_id' => ['nullable', 'exists:spaces,id'],
            'resources.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'resources.*.notes' => ['nullable', 'string'],
        ];
    }
}
