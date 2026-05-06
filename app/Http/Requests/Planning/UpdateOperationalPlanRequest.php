<?php

namespace App\Http\Requests\Planning;

use App\Models\OperationalPlan;
use App\Support\OrganizationScope;
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
        $plan = $this->route('operationalPlan') ?? $this->route('operational_plan');
        $organizationId = $plan instanceof OperationalPlan ? $plan->organization_id : $this->user()?->organization_id;

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'plan_type' => ['sometimes', Rule::in(OperationalPlan::TYPES)],
            'status' => ['nullable', Rule::in(OperationalPlan::STATUSES)],
            'visibility' => ['sometimes', Rule::in(OperationalPlan::VISIBILITIES)],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'owner_user_id' => ['nullable', OrganizationScope::existsRuleForUser('users', $this->user(), organizationId: $organizationId)],
            'department_id' => ['nullable', OrganizationScope::existsRuleForUser('departments', $this->user(), organizationId: $organizationId)],
            'team_id' => ['nullable', OrganizationScope::existsRuleForUser('teams', $this->user(), organizationId: $organizationId)],
            'related_ticket_id' => ['nullable', OrganizationScope::existsRuleForUser('tickets', $this->user(), organizationId: $organizationId)],
            'related_space_id' => ['nullable', OrganizationScope::existsRuleForUser('spaces', $this->user(), organizationId: $organizationId)],
            'budget_estimate' => ['nullable', 'numeric', 'min:0'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
