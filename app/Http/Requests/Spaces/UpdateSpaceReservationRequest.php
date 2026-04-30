<?php

namespace App\Http\Requests\Spaces;

use App\Models\SpaceReservation;
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
        return [
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'event_id' => ['nullable', 'exists:events,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'purpose' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(SpaceReservation::STATUSES)],
        ];
    }
}
