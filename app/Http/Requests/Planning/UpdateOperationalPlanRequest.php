<?php

namespace App\Http\Requests\Planning;

use App\Models\OperationalPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOperationalPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $plan = $this->route('operationalPlan') ?? $this->route('operational_plan');

        return $plan ? $this->user()->can('update', $plan) : $this->user()->can('planning.update');
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'plan_type' => ['sometimes', Rule::in(OperationalPlan::TYPES)],
            'status' => ['nullable', Rule::in(OperationalPlan::STATUSES)],
            'visibility' => ['sometimes', Rule::in(OperationalPlan::VISIBILITIES)],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'related_ticket_id' => ['nullable', 'exists:tickets,id'],
            'related_space_id' => ['nullable', 'exists:spaces,id'],
            'budget_estimate' => ['nullable', 'numeric', 'min:0'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
