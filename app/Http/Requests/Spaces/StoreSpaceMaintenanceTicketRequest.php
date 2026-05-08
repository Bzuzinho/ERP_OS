<?php

namespace App\Http\Requests\Spaces;

use App\Models\Space;
use App\Models\Ticket;
use App\Support\OrganizationScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpaceMaintenanceTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $space = $this->route('space');

        if (! $space instanceof Space) {
            return false;
        }

        return OrganizationScope::sameOrganization($space->organization_id, $this->user())
            && (
                $this->user()->can('spaces.create-maintenance-ticket')
                || $this->user()->can('spaces.manage_maintenance')
                || $this->user()->can('tickets.create')
            );
    }

    public function rules(): array
    {
        $space = $this->route('space');
        $organizationId = $space instanceof Space ? (int) $space->organization_id : null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['nullable', Rule::in(Ticket::PRIORITIES)],
            'department_id' => ['nullable', OrganizationScope::existsRuleForUser('departments', $this->user(), organizationId: $organizationId)],
            'service_area_id' => ['nullable', OrganizationScope::existsRuleForUser('service_areas', $this->user(), organizationId: $organizationId)],
        ];
    }
}
