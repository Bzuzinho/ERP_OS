<?php

namespace App\Http\Requests\Spaces;

use App\Models\Space;
use App\Models\SpaceReservation;
use App\Services\Spaces\SpaceAvailabilityService;
use Illuminate\Foundation\Http\FormRequest;

class StoreSpaceReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', SpaceReservation::class);
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'space_id' => ['required', 'exists:spaces,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'event_id' => ['nullable', 'exists:events,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'purpose' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('space_id') || ! $this->filled('start_at') || ! $this->filled('end_at')) {
                return;
            }

            $space = Space::query()->find($this->integer('space_id'));
            if (! $space) {
                return;
            }

            $availability = app(SpaceAvailabilityService::class)->isAvailable(
                spaceId: $space->id,
                startAt: $this->input('start_at'),
                endAt: $this->input('end_at'),
            );

            if (! $availability['available']) {
                $validator->errors()->add('start_at', 'Existe conflito com outra reserva aprovada no periodo indicado.');
            }
        });
    }
}
