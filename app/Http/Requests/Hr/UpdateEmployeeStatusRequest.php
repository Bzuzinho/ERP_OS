<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('update', $this->employee) ?? false;
    }

    public function rules(): array
    {
        return [
            'is_active' => ['boolean'],
            'end_date' => ['nullable', 'date'],
        ];
    }
}
