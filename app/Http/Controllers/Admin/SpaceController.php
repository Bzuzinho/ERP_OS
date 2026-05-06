<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Spaces\CreateSpaceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Spaces\StoreSpaceRequest;
use App\Http\Requests\Spaces\UpdateSpaceRequest;
use App\Models\Space;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpaceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Space::class);

        $user = $request->user();

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $isPublic = $request->string('is_public')->toString();
        $isActive = $request->string('is_active')->toString();

        $spaces = Space::query()
            ->visibleToUser($user)
            ->when($search, fn ($query) => $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('location_text', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($isPublic !== '', fn ($query) => $query->where('is_public', filter_var($isPublic, FILTER_VALIDATE_BOOLEAN)))
            ->when($isActive !== '', fn ($query) => $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Spaces/Index', [
            'spaces' => $spaces,
            'filters' => compact('search', 'status', 'isPublic', 'isActive'),
            'statuses' => Space::STATUSES,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Space::class);

        return Inertia::render('Admin/Spaces/Create', [
            'statuses' => Space::STATUSES,
        ]);
    }

    public function store(StoreSpaceRequest $request, CreateSpaceAction $createSpaceAction): RedirectResponse
    {
        $space = $createSpaceAction->execute($request->user(), [
            ...$request->validated(),
            'status' => $request->validated('status') ?? 'available',
        ]);

        return to_route('admin.spaces.show', $space)->with('success', 'Espaco criado com sucesso.');
    }

    public function show(Space $space): Response
    {
        $this->authorize('view', $space);

        OrganizationScope::ensureModelBelongsToUserOrganization($space, request()->user());

        $space->load([
            'reservations' => fn ($query) => $query
                ->where('start_at', '>=', now())
                ->orderBy('start_at')
                ->limit(10),
            'maintenanceRecords' => fn ($query) => $query
                ->whereIn('status', ['pending', 'scheduled', 'in_progress'])
                ->latest()
                ->limit(10),
            'cleaningRecords' => fn ($query) => $query
                ->whereIn('status', ['pending', 'scheduled', 'in_progress'])
                ->latest()
                ->limit(10),
            'comments.user:id,name',
            'attachments.uploader:id,name',
            'documents:id,title,related_type,related_id',
        ]);

        return Inertia::render('Admin/Spaces/Show', [
            'space' => $space,
            'statuses' => Space::STATUSES,
        ]);
    }

    public function edit(Space $space): Response
    {
        $this->authorize('update', $space);

        OrganizationScope::ensureModelBelongsToUserOrganization($space, request()->user());

        return Inertia::render('Admin/Spaces/Edit', [
            'space' => $space,
            'statuses' => Space::STATUSES,
        ]);
    }

    public function update(UpdateSpaceRequest $request, Space $space): RedirectResponse
    {
        OrganizationScope::ensureModelBelongsToUserOrganization($space, $request->user());
        $space->update($request->validated());

        return to_route('admin.spaces.show', $space)->with('success', 'Espaco atualizado com sucesso.');
    }

    public function destroy(Space $space): RedirectResponse
    {
        $this->authorize('delete', $space);

        OrganizationScope::ensureModelBelongsToUserOrganization($space, request()->user());

        $space->delete();

        return to_route('admin.spaces.index')->with('success', 'Espaco removido com sucesso.');
    }
}
