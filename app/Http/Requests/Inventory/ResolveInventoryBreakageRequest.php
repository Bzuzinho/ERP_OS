<?php

namespace App\Http\Requests\Inventory;

use App\Models\InventoryBreakage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveInventoryBreakageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('resolve', $this->route('inventoryBreakage'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['resolved', 'written_off', 'cancelled'])],
            'resolution_notes' => ['nullable', 'string'],
        ];
    }
}
