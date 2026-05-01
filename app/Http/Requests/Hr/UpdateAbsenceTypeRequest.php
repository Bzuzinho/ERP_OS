<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAbsenceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('update', $this->absenceType) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'requires_approval' => ['boolean'],
            'is_paid' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
