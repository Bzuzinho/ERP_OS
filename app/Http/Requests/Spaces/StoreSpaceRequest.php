<?php

namespace App\Http\Requests\Spaces;

use App\Models\Space;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Space::class);
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
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
