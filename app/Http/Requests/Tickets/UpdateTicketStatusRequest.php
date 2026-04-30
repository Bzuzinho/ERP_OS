<?php

namespace App\Http\Requests\Tickets;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        if (! $ticket instanceof Ticket) {
            return false;
        }

        $requestedStatus = (string) $this->input('status');
        $closingStatuses = ['fechado', 'cancelado', 'indeferido'];

        if (in_array($requestedStatus, $closingStatuses, true)) {
            return $this->user()->can('close', $ticket) || $this->user()->can('update', $ticket);
        }

        return $this->user()->can('update', $ticket);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Ticket::STATUSES)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
