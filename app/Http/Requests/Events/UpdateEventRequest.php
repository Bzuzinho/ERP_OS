<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_type' => ['required', Rule::in(Event::TYPES)],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'location_text' => ['nullable', 'string', 'max:255'],
            'related_ticket_id' => ['nullable', 'exists:tickets,id'],
            'related_contact_id' => ['nullable', 'exists:contacts,id'],
            'visibility' => ['required', Rule::in(Event::VISIBILITIES)],
        ];
    }
}
