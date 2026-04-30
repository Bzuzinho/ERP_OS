<?php

namespace App\Http\Requests\Contacts;

use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Contact::class);
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'type' => ['required', Rule::in(Contact::TYPES)],
            'name' => ['required', 'string', 'max:255'],
            'nif' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'addresses' => ['nullable', 'array'],
            'addresses.*.type' => ['nullable', 'string', 'max:100'],
            'addresses.*.address' => ['required_with:addresses', 'string'],
            'addresses.*.postal_code' => ['nullable', 'string', 'max:30'],
            'addresses.*.locality' => ['nullable', 'string', 'max:120'],
            'addresses.*.parish' => ['nullable', 'string', 'max:120'],
            'addresses.*.municipality' => ['nullable', 'string', 'max:120'],
            'addresses.*.district' => ['nullable', 'string', 'max:120'],
            'addresses.*.is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
