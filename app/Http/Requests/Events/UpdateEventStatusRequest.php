<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event && $this->user()->can('update', $event);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Event::STATUSES)],
        ];
    }
}
