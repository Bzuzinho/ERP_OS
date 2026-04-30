<?php

namespace App\Http\Requests\Spaces;

use App\Models\SpaceReservation;
use Illuminate\Foundation\Http\FormRequest;

class ApproveSpaceReservationRequest extends FormRequest
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
