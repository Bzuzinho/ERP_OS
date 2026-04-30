<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\ReturnInventoryLoanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\ReturnInventoryLoanRequest;
use App\Models\InventoryLoan;
use Illuminate\Http\RedirectResponse;

class InventoryLoanReturnController extends Controller
{
    public function __invoke(ReturnInventoryLoanRequest $request, InventoryLoan $inventoryLoan, ReturnInventoryLoanAction $action): RedirectResponse
    {
        $action->execute($inventoryLoan, $request->user(), $request->validated('return_notes'));

        return back()->with('success', 'Emprestimo devolvido com sucesso.');
    }
}
