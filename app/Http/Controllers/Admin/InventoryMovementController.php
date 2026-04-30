<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\RegisterInventoryMovementAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreInventoryMovementRequest;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\InventoryMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryMovementController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', InventoryMovement::class);

        $itemId = $request->string('item_id')->toString();
        $movementType = $request->string('movement_type')->toString();
        $date = $request->string('date')->toString();
        $locationId = $request->string('location_id')->toString();

        $movements = InventoryMovement::query()
            ->with(['item:id,name,sku', 'fromLocation:id,name', 'toLocation:id,name', 'handledBy:id,name'])
            ->when($itemId, fn ($query) => $query->where('inventory_item_id', $itemId))
            ->when($movementType, fn ($query) => $query->where('movement_type', $movementType))
            ->when($date, fn ($query) => $query->whereDate('occurred_at', $date))
            ->when($locationId, fn ($query) => $query->where(function ($query) use ($locationId) {
                $query->where('from_location_id', $locationId)->orWhere('to_location_id', $locationId);
            }))
            ->latest('occurred_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/InventoryMovements/Index', [
            'movements' => $movements,
            'items' => InventoryItem::query()->select('id', 'name', 'sku')->orderBy('name')->get(),
            'locations' => InventoryLocation::query()->select('id', 'name')->orderBy('name')->get(),
            'movementTypes' => InventoryMovement::TYPES,
            'filters' => compact('itemId', 'movementType', 'date', 'locationId'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', InventoryMovement::class);

        return Inertia::render('Admin/InventoryMovements/Create', [
            'items' => InventoryItem::query()->select('id', 'name', 'sku', 'current_stock', 'inventory_location_id')->orderBy('name')->get(),
            'locations' => InventoryLocation::query()->select('id', 'name')->orderBy('name')->get(),
            'movementTypes' => InventoryMovement::TYPES,
        ]);
    }

    public function store(StoreInventoryMovementRequest $request, RegisterInventoryMovementAction $action): RedirectResponse
    {
        $validated = $request->validated();
        $item = InventoryItem::query()->findOrFail($validated['inventory_item_id']);

        try {
            $movement = $action->execute($item, $request->user(), $validated);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['movement' => $exception->getMessage()]);
        }

        return to_route('admin.inventory-movements.show', $movement)->with('success', 'Movimento registado com sucesso.');
    }

    public function show(InventoryMovement $inventoryMovement): Response
    {
        $this->authorize('view', $inventoryMovement);

        $inventoryMovement->load([
            'item:id,name,sku',
            'fromLocation:id,name',
            'toLocation:id,name',
            'requestedBy:id,name',
            'handledBy:id,name',
            'relatedTicket:id,reference,title',
            'relatedTask:id,title',
            'relatedEvent:id,title',
            'relatedSpace:id,name',
            'relatedSpaceReservation:id,purpose,status',
            'comments.user:id,name',
            'attachments.uploader:id,name',
        ]);

        return Inertia::render('Admin/InventoryMovements/Show', [
            'movement' => $inventoryMovement,
        ]);
    }
}
