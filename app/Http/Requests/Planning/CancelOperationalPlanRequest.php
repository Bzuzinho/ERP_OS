<?php

namespace App\Http\Requests\Planning;

use Illuminate\Foundation\Http\FormRequest;

class CancelOperationalPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('planning.cancel');
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string'],
        ];
    }
}
