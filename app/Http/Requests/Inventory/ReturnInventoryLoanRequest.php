<?php

namespace App\Http\Requests\Inventory;

use App\Models\InventoryLoan;
use Illuminate\Foundation\Http\FormRequest;

class ReturnInventoryLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('return', $this->route('inventoryLoan'));
    }

    public function rules(): array
    {
        return [
            'return_notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $loan = $this->route('inventoryLoan');

            if (! $loan instanceof InventoryLoan) {
                return;
            }

            if (! in_array($loan->status, ['active', 'overdue'], true)) {
                $validator->errors()->add('loan', 'Apenas emprestimos ativos ou em atraso podem ser devolvidos.');
            }
        });
    }
}
