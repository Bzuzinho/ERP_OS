<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\ReportInventoryBreakageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreInventoryBreakageRequest;
use App\Models\InventoryBreakage;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryBreakageController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', InventoryBreakage::class);

        $status = $request->string('status')->toString();
        $breakageType = $request->string('breakage_type')->toString();
        $itemId = $request->string('item_id')->toString();

        $breakages = InventoryBreakage::query()
            ->with(['item:id,name,sku', 'reporter:id,name', 'resolver:id,name'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($breakageType, fn ($query) => $query->where('breakage_type', $breakageType))
            ->when($itemId, fn ($query) => $query->where('inventory_item_id', $itemId))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/InventoryBreakages/Index', [
            'breakages' => $breakages,
            'items' => InventoryItem::query()->select('id', 'name', 'sku')->orderBy('name')->get(),
            'statuses' => InventoryBreakage::STATUSES,
            'breakageTypes' => InventoryBreakage::TYPES,
            'filters' => compact('status', 'breakageType', 'itemId'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', InventoryBreakage::class);

        return Inertia::render('Admin/InventoryBreakages/Create', [
            'items' => InventoryItem::query()->select('id', 'name', 'sku', 'current_stock')->orderBy('name')->get(),
            'breakageTypes' => InventoryBreakage::TYPES,
        ]);
    }

    public function store(StoreInventoryBreakageRequest $request, ReportInventoryBreakageAction $action): RedirectResponse
    {
        try {
            $breakage = $action->execute($request->user(), $request->validated());
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['breakage' => $exception->getMessage()]);
        }

        return to_route('admin.inventory-breakages.show', $breakage)->with('success', 'Quebra registada com sucesso.');
    }

    public function show(InventoryBreakage $inventoryBreakage): Response
    {
        $this->authorize('view', $inventoryBreakage);

        $inventoryBreakage->load([
            'item:id,name,sku,unit',
            'movement:id,movement_type,quantity,occurred_at',
            'reporter:id,name',
            'resolver:id,name',
            'relatedTicket:id,reference,title',
            'relatedTask:id,title',
            'comments.user:id,name',
            'attachments.uploader:id,name',
        ]);

        return Inertia::render('Admin/InventoryBreakages/Show', [
            'breakage' => $inventoryBreakage,
            'canResolve' => request()->user()->can('resolve', $inventoryBreakage),
            'statuses' => InventoryBreakage::STATUSES,
        ]);
    }
}
