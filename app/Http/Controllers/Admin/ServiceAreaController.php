<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceAreas\StoreServiceAreaRequest;
use App\Http\Requests\ServiceAreas\UpdateServiceAreaRequest;
use App\Models\ServiceArea;
use App\Models\User;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ServiceAreaController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ServiceArea::class);

        $serviceAreas = ServiceArea::query()
            ->visibleToUser($request->user())
            ->withCount(['users', 'tickets'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/ServiceAreas/Index', [
            'serviceAreas' => $serviceAreas,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', ServiceArea::class);

        return Inertia::render('Admin/ServiceAreas/Create');
    }

    public function store(StoreServiceAreaRequest $request): RedirectResponse
    {
        $this->authorize('create', ServiceArea::class);

        $data = $request->validated();

        $serviceArea = ServiceArea::query()->create([
            ...$data,
            'organization_id' => $request->user()->organization_id,
            'slug' => Str::slug($data['slug'] ?? $data['name']),
            'is_active' => $data['is_active'] ?? true,
        ]);

        return to_route('admin.settings.service-areas.show', $serviceArea)->with('success', 'Area funcional criada com sucesso.');
    }

    public function show(ServiceArea $serviceArea): Response
    {
        $this->authorize('view', $serviceArea);

        OrganizationScope::ensureModelBelongsToUserOrganization($serviceArea, request()->user());

        $serviceArea->load(['users:id,name,email', 'tickets:id,reference,title,status,service_area_id']);

        return Inertia::render('Admin/ServiceAreas/Show', [
            'serviceArea' => $serviceArea,
            'availableUsers' => User::query()
                ->tap(fn ($query) => OrganizationScope::apply($query, request()->user()))
                ->where('is_active', true)
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(ServiceArea $serviceArea): Response
    {
        $this->authorize('update', $serviceArea);

        OrganizationScope::ensureModelBelongsToUserOrganization($serviceArea, request()->user());

        return Inertia::render('Admin/ServiceAreas/Edit', [
            'serviceArea' => $serviceArea,
        ]);
    }

    public function update(UpdateServiceAreaRequest $request, ServiceArea $serviceArea): RedirectResponse
    {
        $this->authorize('update', $serviceArea);

        OrganizationScope::ensureModelBelongsToUserOrganization($serviceArea, $request->user());

        $data = $request->validated();

        $serviceArea->update([
            ...$data,
            'slug' => Str::slug($data['slug'] ?? $data['name']),
        ]);

        return to_route('admin.settings.service-areas.show', $serviceArea)->with('success', 'Area funcional atualizada com sucesso.');
    }

    public function destroy(ServiceArea $serviceArea): RedirectResponse
    {
        $this->authorize('delete', $serviceArea);

        OrganizationScope::ensureModelBelongsToUserOrganization($serviceArea, request()->user());

        $serviceArea->delete();

        return to_route('admin.settings.service-areas.index')->with('success', 'Area funcional removida com sucesso.');
    }
}
