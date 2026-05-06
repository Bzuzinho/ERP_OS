<?php

namespace App\Http\Requests\Tickets;

use App\Models\Ticket;
use App\Support\OrganizationScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Ticket::class);
    }

    public function rules(): array
    {
        $user = $this->user();
        $organizationId = $this->resolvedOrganizationId();

        return [
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'contact_id' => ['nullable', OrganizationScope::existsRuleForUser('contacts', $user, organizationId: $organizationId)],
            'assigned_to' => ['nullable', OrganizationScope::existsRuleForUser('users', $user, organizationId: $organizationId)],
            'department_id' => ['nullable', OrganizationScope::existsRuleForUser('departments', $user, organizationId: $organizationId)],
            'service_area_id' => ['nullable', OrganizationScope::existsRuleForUser('service_areas', $user, organizationId: $organizationId)],
            'team_id' => ['nullable', OrganizationScope::existsRuleForUser('teams', $user, organizationId: $organizationId)],
            'category' => ['nullable', 'string', 'max:120'],
            'subcategory' => ['nullable', 'string', 'max:120'],
            'priority' => ['required', Rule::in(Ticket::PRIORITIES)],
            'status' => ['sometimes', Rule::in(Ticket::STATUSES)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location_text' => ['nullable', 'string'],
            'source' => ['required', Rule::in(Ticket::SOURCES)],
            'visibility' => ['required', Rule::in(['internal', 'public'])],
            'due_date' => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->resolvedOrganizationId() !== null) {
                return;
            }

            if ($this->filled('organization_id') || ! $this->user()?->hasRole('super_admin')) {
                return;
            }

            $hasScopedForeignKeys = collect([
                'contact_id',
                'assigned_to',
                'department_id',
                'service_area_id',
                'team_id',
            ])->contains(fn (string $field) => $this->filled($field));

            if ($hasScopedForeignKeys) {
                $validator->errors()->add('organization_id', 'Selecione uma organização válida para esta operação.');
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
