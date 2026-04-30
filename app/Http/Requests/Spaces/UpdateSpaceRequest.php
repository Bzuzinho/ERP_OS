<?php

namespace App\Http\Requests\Spaces;

use App\Models\Space;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('space'));
    }

    public function rules(): array
    {
        /** @var Space $space */
        $space = $this->route('space');

        return [
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('spaces', 'slug')->where('organization_id', $space?->organization_id)->ignore($space?->id)],
            'description' => ['nullable', 'string'],
            'location_text' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(Space::STATUSES)],
            'requires_approval' => ['sometimes', 'boolean'],
            'has_cleaning_required' => ['sometimes', 'boolean'],
            'has_deposit' => ['sometimes', 'boolean'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'rules' => ['nullable', 'string'],
            'is_public' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
