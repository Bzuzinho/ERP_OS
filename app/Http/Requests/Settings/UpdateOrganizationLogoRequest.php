<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ];
    }
}
