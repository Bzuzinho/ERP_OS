<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.update');
    }

    public function rules(): array
    {
        /** @var User $target */
        $target = $this->route('user');

        return [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target->id)],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'is_active'       => ['boolean'],
            'nif'             => ['nullable', 'string', 'max:20'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'address'         => ['nullable', 'string', 'max:500'],
            'birth_date'      => ['nullable', 'date', 'before:today'],
        ];
    }
}
