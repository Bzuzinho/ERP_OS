<?php

namespace App\Http\Requests\Tickets;

use App\Models\Ticket;
use App\Support\OrganizationScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket && $this->user()->can('update', $ticket);
    }

    public function rules(): array
    {
        $ticket = $this->route('ticket');
        $organizationId = $ticket instanceof Ticket ? $ticket->organization_id : $this->user()?->organization_id;

        return [
            'contact_id' => ['nullable', OrganizationScope::existsRuleForUser('contacts', $this->user(), organizationId: $organizationId)],
            'assigned_to' => ['nullable', OrganizationScope::existsRuleForUser('users', $this->user(), organizationId: $organizationId)],
            'department_id' => ['nullable', OrganizationScope::existsRuleForUser('departments', $this->user(), organizationId: $organizationId)],
            'service_area_id' => ['nullable', OrganizationScope::existsRuleForUser('service_areas', $this->user(), organizationId: $organizationId)],
            'team_id' => ['nullable', OrganizationScope::existsRuleForUser('teams', $this->user(), organizationId: $organizationId)],
            'category' => ['nullable', 'string', 'max:120'],
            'subcategory' => ['nullable', 'string', 'max:120'],
            'priority' => ['required', Rule::in(Ticket::PRIORITIES)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location_text' => ['nullable', 'string'],
            'source' => ['required', Rule::in(Ticket::SOURCES)],
            'visibility' => ['required', Rule::in(['internal', 'public'])],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
