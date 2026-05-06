<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use App\Support\OrganizationScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event && $this->user()->can('update', $event);
    }

    public function rules(): array
    {
        $event = $this->route('event');
        $organizationId = $event instanceof Event ? $event->organization_id : $this->user()?->organization_id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_type' => ['required', Rule::in(Event::TYPES)],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'location_text' => ['nullable', 'string', 'max:255'],
            'related_ticket_id' => ['nullable', OrganizationScope::existsRuleForUser('tickets', $this->user(), organizationId: $organizationId)],
            'related_contact_id' => ['nullable', OrganizationScope::existsRuleForUser('contacts', $this->user(), organizationId: $organizationId)],
            'visibility' => ['required', Rule::in(Event::VISIBILITIES)],
        ];
    }
}
