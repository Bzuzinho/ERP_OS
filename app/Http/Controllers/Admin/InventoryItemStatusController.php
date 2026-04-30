<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\UpdateInventoryItemStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\UpdateInventoryItemStatusRequest;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;

class InventoryItemStatusController extends Controller
{
    public function update(UpdateInventoryItemStatusRequest $request, InventoryItem $inventoryItem, UpdateInventoryItemStatusAction $action): RedirectResponse
    {
        $action->execute($inventoryItem, $request->validated('status'), $request->user());

        return back()->with('success', 'Estado do item atualizado com sucesso.');
    }
}
