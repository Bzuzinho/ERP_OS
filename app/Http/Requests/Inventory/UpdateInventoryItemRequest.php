<?php

namespace App\Http\Requests\Inventory;

use App\Models\InventoryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('inventoryItem'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'inventory_category_id' => ['nullable', 'exists:inventory_categories,id'],
            'inventory_location_id' => ['nullable', 'exists:inventory_locations,id'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:120'],
            'item_type' => ['required', Rule::in(InventoryItem::ITEM_TYPES)],
            'unit' => ['required', Rule::in(InventoryItem::UNITS)],
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'maximum_stock' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(InventoryItem::STATUSES)],
            'is_stock_tracked' => ['sometimes', 'boolean'],
            'is_loanable' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
