<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\ApproveInventoryRestockRequestAction;
use App\Actions\Inventory\CompleteInventoryRestockRequestAction;
use App\Actions\Inventory\RejectInventoryRestockRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\ApproveInventoryRestockRequestRequest;
use App\Http\Requests\Inventory\CompleteInventoryRestockRequestRequest;
use App\Http\Requests\Inventory\RejectInventoryRestockRequestRequest;
use App\Models\InventoryRestockRequest;
use Illuminate\Http\RedirectResponse;

class InventoryRestockApprovalController extends Controller
{
    public function approve(ApproveInventoryRestockRequestRequest $request, InventoryRestockRequest $inventoryRestockRequest, ApproveInventoryRestockRequestAction $action): RedirectResponse
    {
        $action->execute(
            $inventoryRestockRequest,
            $request->user(),
            (float) $request->validated('quantity_approved'),
            $request->validated('notes'),
        );

        return back()->with('success', 'Pedido de reposicao aprovado com sucesso.');
    }

    public function reject(RejectInventoryRestockRequestRequest $request, InventoryRestockRequest $inventoryRestockRequest, RejectInventoryRestockRequestAction $action): RedirectResponse
    {
        $action->execute(
            $inventoryRestockRequest,
            $request->user(),
            $request->validated('rejection_reason'),
        );

        return back()->with('success', 'Pedido de reposicao rejeitado com sucesso.');
    }

    public function complete(CompleteInventoryRestockRequestRequest $request, InventoryRestockRequest $inventoryRestockRequest, CompleteInventoryRestockRequestAction $action): RedirectResponse
    {
        $action->execute(
            $inventoryRestockRequest,
            $request->user(),
            $request->validated('notes'),
        );

        return back()->with('success', 'Pedido de reposicao concluido com sucesso.');
    }
}
