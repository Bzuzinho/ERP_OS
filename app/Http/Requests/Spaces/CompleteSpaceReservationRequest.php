<?php

namespace App\Http\Requests\Spaces;

use Illuminate\Foundation\Http\FormRequest;

class CompleteSpaceReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('approve', $this->route('spaceReservation'));
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string'],
        ];
    }
}
