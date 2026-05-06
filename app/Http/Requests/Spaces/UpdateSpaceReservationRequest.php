<?php

namespace App\Http\Requests\Spaces;

use App\Models\SpaceReservation;
use App\Support\OrganizationScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpaceReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('spaceReservation'));
    }

    public function rules(): array
    {
        $reservation = $this->route('spaceReservation');
        $organizationId = $reservation instanceof SpaceReservation ? $reservation->organization_id : $this->user()?->organization_id;

        return [
            'contact_id' => ['nullable', OrganizationScope::existsRuleForUser('contacts', $this->user(), organizationId: $organizationId)],
            'event_id' => ['nullable', OrganizationScope::existsRuleForUser('events', $this->user(), organizationId: $organizationId)],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'purpose' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(SpaceReservation::STATUSES)],
        ];
    }
}
