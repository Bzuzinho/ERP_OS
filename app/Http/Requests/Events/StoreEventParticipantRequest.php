<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event && $this->user()->can('update', $event);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'role' => ['nullable', 'string', 'max:150'],
            'attendance_status' => ['nullable', Rule::in(Event::ATTENDANCE_STATUSES)],
        ];
    }
}
