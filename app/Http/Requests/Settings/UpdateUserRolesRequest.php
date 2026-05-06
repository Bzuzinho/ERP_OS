<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.manage_roles');
    }

    public function rules(): array
    {
        return [
            'roles'   => ['required', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->user()?->hasRole('super_admin')) {
                return;
            }

            $roles = collect($this->input('roles', []));

            if ($roles->contains('super_admin')) {
                $validator->errors()->add('roles', 'Apenas o super_admin pode atribuir ou remover o perfil super_admin.');
            }
        });
    }
}
