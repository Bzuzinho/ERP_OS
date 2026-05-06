<?php

namespace App\Http\Requests\Tickets;

use App\Models\Ticket;
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
        return [
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'service_area_id' => ['nullable', 'exists:service_areas,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
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
