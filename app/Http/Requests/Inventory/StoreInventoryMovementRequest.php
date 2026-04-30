<?php

namespace App\Http\Requests\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Services\Inventory\InventoryStockService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', InventoryMovement::class);
    }

    public function rules(): array
    {
        return [
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'movement_type' => ['required', Rule::in(InventoryMovement::TYPES)],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'from_location_id' => ['nullable', 'exists:inventory_locations,id'],
            'to_location_id' => ['nullable', 'exists:inventory_locations,id'],
            'related_ticket_id' => ['nullable', 'exists:tickets,id'],
            'related_task_id' => ['nullable', 'exists:tasks,id'],
            'related_event_id' => ['nullable', 'exists:events,id'],
            'related_space_id' => ['nullable', 'exists:spaces,id'],
            'related_space_reservation_id' => ['nullable', 'exists:space_reservations,id'],
            'notes' => ['nullable', 'string'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('inventory_item_id') || ! $this->filled('quantity') || ! $this->filled('movement_type')) {
                return;
            }

            $item = InventoryItem::query()->find($this->integer('inventory_item_id'));

            if (! $item) {
                return;
            }

            $type = (string) $this->input('movement_type');
            $quantity = (float) $this->input('quantity');

            if (in_array($type, ['exit', 'consumption', 'loan', 'breakage'], true)
                && ! app(InventoryStockService::class)->hasSufficientStock($item, $quantity)) {
                $validator->errors()->add('quantity', 'Stock insuficiente para este movimento.');
            }
        });
    }
}
