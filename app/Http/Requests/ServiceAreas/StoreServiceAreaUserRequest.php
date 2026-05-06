<?php

namespace App\Http\Requests\ServiceAreas;

use App\Models\ServiceArea;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceAreaUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $serviceArea = $this->route('service_area') ?? $this->route('serviceArea');

        return $serviceArea instanceof ServiceArea && $this->user()->can('manageUsers', $serviceArea);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['nullable', 'string', 'max:120'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
