<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\CreateInventoryItemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreInventoryItemRequest;
use App\Http\Requests\Inventory\UpdateInventoryItemRequest;
use App\Models\InventoryBreakage;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\InventoryLocation;
use App\Models\InventoryMovement;
use App\Models\InventoryRestockRequest;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryItemController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', InventoryItem::class);

        $user = $request->user();

        $search = $request->string('search')->toString();
        $categoryId = $request->string('category_id')->toString();
        $locationId = $request->string('location_id')->toString();
        $itemType = $request->string('item_type')->toString();
        $status = $request->string('status')->toString();
        $lowStock = $request->boolean('low_stock');

        $items = InventoryItem::query()
            ->visibleToUser($user)
            ->with(['category:id,name', 'location:id,name'])
            ->when($search, fn ($query) => $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            }))
            ->when($categoryId, fn ($query) => $query->where('inventory_category_id', $categoryId))
            ->when($locationId, fn ($query) => $query->where('inventory_location_id', $locationId))
            ->when($itemType, fn ($query) => $query->where('item_type', $itemType))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($lowStock, fn ($query) => $query
                ->whereNotNull('minimum_stock')
                ->whereColumn('current_stock', '<', 'minimum_stock'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/InventoryItems/Index', [
            'items' => $items,
            'categories' => OrganizationScope::apply(InventoryCategory::query(), $user)->select('id', 'name')->orderBy('name')->get(),
            'locations' => OrganizationScope::apply(InventoryLocation::query(), $user)->select('id', 'name')->orderBy('name')->get(),
            'itemTypes' => InventoryItem::ITEM_TYPES,
            'statuses' => InventoryItem::STATUSES,
            'filters' => compact('search', 'categoryId', 'locationId', 'itemType', 'status', 'lowStock'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', InventoryItem::class);

        $user = request()->user();

        return Inertia::render('Admin/InventoryItems/Create', [
            'categories' => OrganizationScope::apply(InventoryCategory::query(), $user)->select('id', 'name')->orderBy('name')->get(),
            'locations' => OrganizationScope::apply(InventoryLocation::query(), $user)->select('id', 'name')->orderBy('name')->get(),
            'itemTypes' => InventoryItem::ITEM_TYPES,
            'units' => InventoryItem::UNITS,
            'statuses' => InventoryItem::STATUSES,
        ]);
    }

    public function store(StoreInventoryItemRequest $request, CreateInventoryItemAction $action): RedirectResponse
    {
        $item = $action->execute($request->user(), $request->validated());

        return to_route('admin.inventory-items.show', $item)->with('success', 'Item criado com sucesso.');
    }

    public function show(InventoryItem $inventoryItem): Response
    {
        $this->authorize('view', $inventoryItem);

        OrganizationScope::ensureModelBelongsToUserOrganization($inventoryItem, request()->user());

        $inventoryItem->load([
            'category:id,name',
            'location:id,name',
            'movements' => fn ($query) => $query->latest()->limit(20),
            'movements.handledBy:id,name',
            'loans' => fn ($query) => $query->latest()->limit(20),
            'loans.borrowerUser:id,name',
            'loans.borrowerContact:id,name',
            'restockRequests' => fn ($query) => $query->latest()->limit(20),
            'breakages' => fn ($query) => $query->latest()->limit(20),
            'comments.user:id,name',
            'attachments.uploader:id,name',
        ]);

        return Inertia::render('Admin/InventoryItems/Show', [
            'item' => $inventoryItem,
            'stockStatus' => (float) $inventoryItem->current_stock <= 0 ? 'out' : ($inventoryItem->minimum_stock !== null && (float) $inventoryItem->current_stock < (float) $inventoryItem->minimum_stock ? 'low' : 'ok'),
            'can' => [
                'move' => request()->user()->can('create', InventoryMovement::class),
                'loan' => request()->user()->can('create', InventoryLoan::class),
                'breakage' => request()->user()->can('create', InventoryBreakage::class),
                'restock' => request()->user()->can('create', InventoryRestockRequest::class),
            ],
        ]);
    }

    public function edit(InventoryItem $inventoryItem): Response
    {
        $this->authorize('update', $inventoryItem);

        $user = request()->user();
        OrganizationScope::ensureModelBelongsToUserOrganization($inventoryItem, $user);

        return Inertia::render('Admin/InventoryItems/Edit', [
            'item' => $inventoryItem,
            'categories' => OrganizationScope::apply(InventoryCategory::query(), $user)->select('id', 'name')->orderBy('name')->get(),
            'locations' => OrganizationScope::apply(InventoryLocation::query(), $user)->select('id', 'name')->orderBy('name')->get(),
            'itemTypes' => InventoryItem::ITEM_TYPES,
            'units' => InventoryItem::UNITS,
            'statuses' => InventoryItem::STATUSES,
        ]);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem): RedirectResponse
    {
        OrganizationScope::ensureModelBelongsToUserOrganization($inventoryItem, $request->user());
        $inventoryItem->update($request->validated());

        return to_route('admin.inventory-items.show', $inventoryItem)->with('success', 'Item atualizado com sucesso.');
    }

    public function destroy(InventoryItem $inventoryItem): RedirectResponse
    {
        $this->authorize('delete', $inventoryItem);

        OrganizationScope::ensureModelBelongsToUserOrganization($inventoryItem, request()->user());

        $inventoryItem->delete();

        return to_route('admin.inventory-items.index')->with('success', 'Item removido com sucesso.');
    }
}
