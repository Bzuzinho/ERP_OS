<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', 'unique:users,email'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'password'        => ['nullable', 'string', 'min:8'],
            'roles'           => ['nullable', 'array'],
            'roles.*'         => ['string', Rule::exists('roles', 'name')],
            'is_active'       => ['boolean'],
        ];
    }
}
