<?php

namespace App\Http\Requests\ServiceAreas;

use App\Models\ServiceArea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ServiceArea::class);
    }

    public function rules(): array
    {
        $organizationId = $this->user()->organization_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('service_areas', 'slug')->where('organization_id', $organizationId),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
