<?php

namespace App\Http\Requests\Settings;

use App\Support\OrganizationScope;
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
        $user = $this->user();

        return [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', 'unique:users,email'],
            'organization_id' => [
                'nullable',
                'exists:organizations,id',
                Rule::requiredIf(fn () => OrganizationScope::canBypassOrganizationScope($user)),
            ],
            'password'        => ['nullable', 'string', 'min:8'],
            'nif'             => ['nullable', 'string', 'max:20'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'address'         => ['nullable', 'string', 'max:500'],
            'birth_date'      => ['nullable', 'date', 'before:today'],
            'roles'           => ['nullable', 'array'],
            'roles.*'         => ['string', Rule::exists('roles', 'name')],
            'is_active'       => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('organization_id') || OrganizationScope::canBypassOrganizationScope($this->user())) {
            return;
        }

        $this->merge([
            'organization_id' => $this->user()?->organization_id,
        ]);
    }
}
