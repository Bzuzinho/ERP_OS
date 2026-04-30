<?php

namespace App\Http\Requests\Inventory;

use App\Models\InventoryBreakage;
use App\Models\InventoryItem;
use App\Services\Inventory\InventoryStockService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryBreakageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', InventoryBreakage::class);
    }

    public function rules(): array
    {
        return [
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'breakage_type' => ['required', Rule::in(InventoryBreakage::TYPES)],
            'description' => ['nullable', 'string'],
            'related_ticket_id' => ['nullable', 'exists:tickets,id'],
            'related_task_id' => ['nullable', 'exists:tasks,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('inventory_item_id') || ! $this->filled('quantity')) {
                return;
            }

            $item = InventoryItem::query()->find($this->integer('inventory_item_id'));
            if (! $item) {
                return;
            }

            if ($item->is_stock_tracked && ! app(InventoryStockService::class)->hasSufficientStock($item, (float) $this->input('quantity'))) {
                $validator->errors()->add('quantity', 'Stock insuficiente para registar quebra.');
            }
        });
    }
}
