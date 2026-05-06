<?php

namespace App\Http\Requests\Planning;

use App\Models\OperationalPlan;
use App\Support\OrganizationScope;
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
        $organizationId = $this->user()?->organization_id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'plan_type' => ['required', Rule::in(OperationalPlan::TYPES)],
            'status' => ['nullable', Rule::in(OperationalPlan::STATUSES)],
            'visibility' => ['required', Rule::in(OperationalPlan::VISIBILITIES)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'owner_user_id' => ['nullable', OrganizationScope::existsRuleForUser('users', $this->user(), organizationId: $organizationId)],
            'department_id' => ['nullable', OrganizationScope::existsRuleForUser('departments', $this->user(), organizationId: $organizationId)],
            'team_id' => ['nullable', OrganizationScope::existsRuleForUser('teams', $this->user(), organizationId: $organizationId)],
            'related_ticket_id' => ['nullable', OrganizationScope::existsRuleForUser('tickets', $this->user(), organizationId: $organizationId)],
            'related_space_id' => ['nullable', OrganizationScope::existsRuleForUser('spaces', $this->user(), organizationId: $organizationId)],
            'budget_estimate' => ['nullable', 'numeric', 'min:0'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'participants' => ['nullable', 'array'],
            'participants.*.user_id' => ['nullable', OrganizationScope::existsRuleForUser('users', $this->user(), organizationId: $organizationId)],
            'participants.*.employee_id' => ['nullable', OrganizationScope::existsRuleForUser('employees', $this->user(), organizationId: $organizationId)],
            'participants.*.team_id' => ['nullable', OrganizationScope::existsRuleForUser('teams', $this->user(), organizationId: $organizationId)],
            'participants.*.role' => ['nullable', 'string', 'max:255'],
            'resources' => ['nullable', 'array'],
            'resources.*.inventory_item_id' => ['nullable', OrganizationScope::existsRuleForUser('inventory_items', $this->user(), organizationId: $organizationId)],
            'resources.*.space_id' => ['nullable', OrganizationScope::existsRuleForUser('spaces', $this->user(), organizationId: $organizationId)],
            'resources.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'resources.*.notes' => ['nullable', 'string'],
        ];
    }
}
