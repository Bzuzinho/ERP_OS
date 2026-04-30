<?php

namespace App\Http\Requests\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Services\Inventory\InventoryStockService;
use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', InventoryLoan::class);
    }

    public function rules(): array
    {
        return [
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'borrower_user_id' => ['nullable', 'exists:users,id'],
            'borrower_contact_id' => ['nullable', 'exists:contacts,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'loaned_at' => ['nullable', 'date'],
            'expected_return_at' => ['nullable', 'date', 'after:loaned_at'],
            'related_ticket_id' => ['nullable', 'exists:tickets,id'],
            'related_task_id' => ['nullable', 'exists:tasks,id'],
            'related_event_id' => ['nullable', 'exists:events,id'],
            'related_space_reservation_id' => ['nullable', 'exists:space_reservations,id'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasUser = $this->filled('borrower_user_id');
            $hasContact = $this->filled('borrower_contact_id');

            if (! $hasUser && ! $hasContact) {
                $validator->errors()->add('borrower_user_id', 'Indique utilizador ou contacto para o emprestimo.');
            }

            if (! $this->filled('inventory_item_id') || ! $this->filled('quantity')) {
                return;
            }

            $item = InventoryItem::query()->find($this->integer('inventory_item_id'));
            if (! $item) {
                return;
            }

            if (! $item->is_loanable) {
                $validator->errors()->add('inventory_item_id', 'O item selecionado nao esta disponivel para emprestimo.');
            }

            if ($item->is_stock_tracked && ! app(InventoryStockService::class)->hasSufficientStock($item, (float) $this->input('quantity'))) {
                $validator->errors()->add('quantity', 'Stock insuficiente para emprestimo.');
            }
        });
    }
}
