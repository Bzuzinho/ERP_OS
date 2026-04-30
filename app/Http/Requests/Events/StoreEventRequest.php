<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_type' => ['required', Rule::in(Event::TYPES)],
            'status' => ['sometimes', Rule::in(Event::STATUSES)],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'location_text' => ['nullable', 'string', 'max:255'],
            'related_ticket_id' => ['nullable', 'exists:tickets,id'],
            'related_contact_id' => ['nullable', 'exists:contacts,id'],
            'visibility' => ['required', Rule::in(Event::VISIBILITIES)],
            'participants' => ['nullable', 'array'],
            'participants.*.user_id' => ['nullable', 'exists:users,id'],
            'participants.*.contact_id' => ['nullable', 'exists:contacts,id'],
            'participants.*.role' => ['nullable', 'string', 'max:150'],
            'participants.*.attendance_status' => ['nullable', Rule::in(Event::ATTENDANCE_STATUSES)],
        ];
    }
}
