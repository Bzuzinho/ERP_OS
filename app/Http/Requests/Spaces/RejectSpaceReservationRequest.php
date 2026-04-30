<?php

namespace App\Http\Requests\Spaces;

use Illuminate\Foundation\Http\FormRequest;

class RejectSpaceReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('approve', $this->route('spaceReservation'));
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
