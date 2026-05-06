<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        $orgId = $this->user()->organization_id;

        return [
            'name'            => ['required', 'string', 'max:255'],
            'code'            => ['nullable', 'string', 'max:20', Rule::unique('organizations', 'code')->ignore($orgId)],
            'nif'             => ['nullable', 'string', 'max:20'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'phone_secondary' => ['nullable', 'string', 'max:30'],
            'fax'             => ['nullable', 'string', 'max:30'],
            'website'         => ['nullable', 'url', 'max:255'],
            'address'         => ['nullable', 'string', 'max:500'],
            'postal_code'     => ['nullable', 'string', 'max:20'],
            'city'            => ['nullable', 'string', 'max:100'],
            'county'          => ['nullable', 'string', 'max:100'],
            'district'        => ['nullable', 'string', 'max:100'],
            'country'         => ['nullable', 'string', 'max:100'],
            'president_name'  => ['nullable', 'string', 'max:255'],
            'iban'            => ['nullable', 'string', 'max:34'],
            'facebook_url'    => ['nullable', 'url', 'max:255'],
            'instagram_url'   => ['nullable', 'url', 'max:255'],
            'description'     => ['nullable', 'string', 'max:2000'],
        ];
    }
}
