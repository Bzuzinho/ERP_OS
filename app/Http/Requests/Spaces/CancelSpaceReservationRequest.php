<?php

namespace App\Http\Requests\Spaces;

use Illuminate\Foundation\Http\FormRequest;

class CancelSpaceReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('cancel', $this->route('spaceReservation'));
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => [$this->routeIs('portal.*') ? 'required' : 'nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
