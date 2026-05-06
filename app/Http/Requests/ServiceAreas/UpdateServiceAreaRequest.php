<?php

namespace App\Http\Requests\ServiceAreas;

use App\Models\ServiceArea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $serviceArea = $this->route('service_area') ?? $this->route('serviceArea');

        return $serviceArea instanceof ServiceArea && $this->user()->can('update', $serviceArea);
    }

    public function rules(): array
    {
        /** @var ServiceArea $serviceArea */
        $serviceArea = $this->route('service_area') ?? $this->route('serviceArea');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('service_areas', 'slug')
                    ->where('organization_id', $this->user()->organization_id)
                    ->ignore($serviceArea->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
