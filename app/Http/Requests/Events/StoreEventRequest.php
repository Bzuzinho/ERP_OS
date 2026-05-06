<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use App\Support\OrganizationScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Event::class);
    }

    public function rules(): array
    {
        $organizationId = $this->resolvedOrganizationId();

        return [
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_type' => ['required', Rule::in(Event::TYPES)],
            'status' => ['sometimes', Rule::in(Event::STATUSES)],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'location_text' => ['nullable', 'string', 'max:255'],
            'related_ticket_id' => ['nullable', OrganizationScope::existsRuleForUser('tickets', $this->user(), organizationId: $organizationId)],
            'related_contact_id' => ['nullable', OrganizationScope::existsRuleForUser('contacts', $this->user(), organizationId: $organizationId)],
            'visibility' => ['required', Rule::in(Event::VISIBILITIES)],
            'participants' => ['nullable', 'array'],
            'participants.*.user_id' => ['nullable', OrganizationScope::existsRuleForUser('users', $this->user(), organizationId: $organizationId)],
            'participants.*.contact_id' => ['nullable', OrganizationScope::existsRuleForUser('contacts', $this->user(), organizationId: $organizationId)],
            'participants.*.role' => ['nullable', 'string', 'max:150'],
            'participants.*.attendance_status' => ['nullable', Rule::in(Event::ATTENDANCE_STATUSES)],
        ];
    }

    private function resolvedOrganizationId(): ?int
    {
        $organizationId = $this->input('organization_id');

        if ($organizationId !== null && $organizationId !== '') {
            return (int) $organizationId;
        }

        return $this->user()?->organization_id;
    }
}
