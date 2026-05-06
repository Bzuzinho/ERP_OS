<?php

namespace App\Http\Requests\Spaces;

use App\Models\Space;
use App\Models\SpaceReservation;
use App\Services\Spaces\SpaceAvailabilityService;
use App\Support\OrganizationScope;
use Illuminate\Foundation\Http\FormRequest;

class StoreSpaceReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', SpaceReservation::class);
    }

    public function rules(): array
    {
        $organizationId = $this->resolvedOrganizationId();

        return [
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'space_id' => ['required', OrganizationScope::existsRuleForUser('spaces', $this->user(), organizationId: $organizationId)],
            'contact_id' => ['nullable', OrganizationScope::existsRuleForUser('contacts', $this->user(), organizationId: $organizationId)],
            'event_id' => ['nullable', OrganizationScope::existsRuleForUser('events', $this->user(), organizationId: $organizationId)],
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
            if ($this->resolvedOrganizationId() === null && $this->user()?->hasRole('super_admin')) {
                $validator->errors()->add('organization_id', 'Selecione uma organização válida para esta operação.');

                return;
            }

            if (! $this->filled('space_id') || ! $this->filled('start_at') || ! $this->filled('end_at')) {
                return;
            }

            $space = Space::query()
                ->visibleToUser($this->user())
                ->find($this->integer('space_id'));
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

    private function resolvedOrganizationId(): ?int
    {
        $organizationId = $this->input('organization_id');

        if ($organizationId !== null && $organizationId !== '') {
            return (int) $organizationId;
        }

        return $this->user()?->organization_id;
    }
}
