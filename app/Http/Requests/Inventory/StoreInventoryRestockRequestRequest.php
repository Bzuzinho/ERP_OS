<?php

namespace App\Http\Requests\Inventory;

use App\Models\InventoryRestockRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRestockRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', InventoryRestockRequest::class);
    }

    public function rules(): array
    {
        return [
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity_requested' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
