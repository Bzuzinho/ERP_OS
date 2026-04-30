<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\ResolveInventoryBreakageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\ResolveInventoryBreakageRequest;
use App\Models\InventoryBreakage;
use Illuminate\Http\RedirectResponse;

class InventoryBreakageResolutionController extends Controller
{
    public function __invoke(ResolveInventoryBreakageRequest $request, InventoryBreakage $inventoryBreakage, ResolveInventoryBreakageAction $action): RedirectResponse
    {
        $action->execute(
            $inventoryBreakage,
            $request->user(),
            $request->validated('status'),
            $request->validated('resolution_notes'),
        );

        return back()->with('success', 'Quebra atualizada com sucesso.');
    }
}
