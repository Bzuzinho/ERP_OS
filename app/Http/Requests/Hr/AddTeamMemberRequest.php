<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class AddTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('update', $this->team) ?? false;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'role' => ['nullable', 'string', 'max:255'],
            'joined_at' => ['nullable', 'date'],
        ];
    }
}
