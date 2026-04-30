<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreInventoryCategoryRequest;
use App\Http\Requests\Inventory\UpdateInventoryCategoryRequest;
use App\Models\InventoryCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', InventoryCategory::class);

        $search = $request->string('search')->toString();

        $categories = InventoryCategory::query()
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/InventoryCategories/Index', [
            'categories' => $categories,
            'filters' => compact('search'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', InventoryCategory::class);

        return Inertia::render('Admin/InventoryCategories/Create');
    }

    public function store(StoreInventoryCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $slug = \Illuminate\Support\Str::slug($data['slug'] ?? $data['name']);

        $category = InventoryCategory::create([
            ...$data,
            'organization_id' => $request->user()->organization_id,
            'slug' => $slug,
        ]);

        return to_route('admin.inventory-categories.edit', $category)->with('success', 'Categoria criada com sucesso.');
    }

    public function edit(InventoryCategory $inventoryCategory): Response
    {
        $this->authorize('update', $inventoryCategory);

        return Inertia::render('Admin/InventoryCategories/Edit', [
            'category' => $inventoryCategory,
        ]);
    }

    public function update(UpdateInventoryCategoryRequest $request, InventoryCategory $inventoryCategory): RedirectResponse
    {
        $data = $request->validated();

        $inventoryCategory->update([
            ...$data,
            'slug' => \Illuminate\Support\Str::slug($data['slug'] ?? $data['name']),
        ]);

        return to_route('admin.inventory-categories.edit', $inventoryCategory)->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(InventoryCategory $inventoryCategory): RedirectResponse
    {
        $this->authorize('delete', $inventoryCategory);

        $inventoryCategory->delete();

        return to_route('admin.inventory-categories.index')->with('success', 'Categoria removida com sucesso.');
    }
}
