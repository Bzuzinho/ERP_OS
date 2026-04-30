<?php

namespace App\Http\Requests\Inventory;

use App\Models\InventoryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryItemStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('inventoryItem'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(InventoryItem::STATUSES)],
        ];
    }
}
