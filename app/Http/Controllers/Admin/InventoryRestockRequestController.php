<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\CreateInventoryRestockRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreInventoryRestockRequestRequest;
use App\Models\InventoryItem;
use App\Models\InventoryRestockRequest;
use App\Services\Inventory\InventoryRestockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryRestockRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', InventoryRestockRequest::class);

        $status = $request->string('status')->toString();
        $itemId = $request->string('item_id')->toString();
        $requester = $request->string('requester')->toString();

        $restockRequests = InventoryRestockRequest::query()
            ->with(['item:id,name,sku', 'requester:id,name', 'approver:id,name'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($itemId, fn ($query) => $query->where('inventory_item_id', $itemId))
            ->when($requester, fn ($query) => $query->whereHas('requester', fn ($builder) => $builder->where('name', 'like', "%{$requester}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/InventoryRestockRequests/Index', [
            'restockRequests' => $restockRequests,
            'items' => InventoryItem::query()->select('id', 'name', 'sku')->orderBy('name')->get(),
            'statuses' => InventoryRestockRequest::STATUSES,
            'filters' => compact('status', 'itemId', 'requester'),
        ]);
    }

    public function create(InventoryRestockService $restockService): Response
    {
        $this->authorize('create', InventoryRestockRequest::class);

        $items = InventoryItem::query()->select('id', 'name', 'sku', 'current_stock', 'minimum_stock', 'maximum_stock')->orderBy('name')->get();

        return Inertia::render('Admin/InventoryRestockRequests/Create', [
            'items' => $items,
            'suggestions' => $items->mapWithKeys(fn ($item) => [$item->id => $restockService->suggestQuantity($item)]),
        ]);
    }

    public function store(StoreInventoryRestockRequestRequest $request, CreateInventoryRestockRequestAction $action): RedirectResponse
    {
        $restockRequest = $action->execute($request->user(), $request->validated());

        return to_route('admin.inventory-restock-requests.show', $restockRequest)->with('success', 'Pedido de reposicao criado com sucesso.');
    }

    public function show(InventoryRestockRequest $inventoryRestockRequest): Response
    {
        $this->authorize('view', $inventoryRestockRequest);

        $inventoryRestockRequest->load([
            'item:id,name,sku,unit,current_stock,minimum_stock,maximum_stock',
            'requester:id,name',
            'approver:id,name',
            'comments.user:id,name',
            'attachments.uploader:id,name',
        ]);

        return Inertia::render('Admin/InventoryRestockRequests/Show', [
            'restockRequest' => $inventoryRestockRequest,
            'can' => [
                'approve' => request()->user()->can('approve', $inventoryRestockRequest),
                'reject' => request()->user()->can('reject', $inventoryRestockRequest),
                'complete' => request()->user()->can('complete', $inventoryRestockRequest),
            ],
        ]);
    }
}
