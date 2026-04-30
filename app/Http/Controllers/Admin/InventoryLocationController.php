<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreInventoryLocationRequest;
use App\Http\Requests\Inventory\UpdateInventoryLocationRequest;
use App\Models\InventoryLocation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryLocationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', InventoryLocation::class);

        $search = $request->string('search')->toString();

        $locations = InventoryLocation::query()
            ->with('responsibleUser:id,name')
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/InventoryLocations/Index', [
            'locations' => $locations,
            'filters' => compact('search'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', InventoryLocation::class);

        return Inertia::render('Admin/InventoryLocations/Create', [
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreInventoryLocationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $location = InventoryLocation::create([
            ...$data,
            'organization_id' => $request->user()->organization_id,
            'slug' => \Illuminate\Support\Str::slug($data['slug'] ?? $data['name']),
        ]);

        return to_route('admin.inventory-locations.edit', $location)->with('success', 'Localizacao criada com sucesso.');
    }

    public function edit(InventoryLocation $inventoryLocation): Response
    {
        $this->authorize('update', $inventoryLocation);

        return Inertia::render('Admin/InventoryLocations/Edit', [
            'location' => $inventoryLocation,
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateInventoryLocationRequest $request, InventoryLocation $inventoryLocation): RedirectResponse
    {
        $data = $request->validated();

        $inventoryLocation->update([
            ...$data,
            'slug' => \Illuminate\Support\Str::slug($data['slug'] ?? $data['name']),
        ]);

        return to_route('admin.inventory-locations.edit', $inventoryLocation)->with('success', 'Localizacao atualizada com sucesso.');
    }

    public function destroy(InventoryLocation $inventoryLocation): RedirectResponse
    {
        $this->authorize('delete', $inventoryLocation);

        $inventoryLocation->delete();

        return to_route('admin.inventory-locations.index')->with('success', 'Localizacao removida com sucesso.');
    }
}
