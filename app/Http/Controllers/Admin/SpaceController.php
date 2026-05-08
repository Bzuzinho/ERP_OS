<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Spaces\CreateSpaceAction;
use App\Actions\Spaces\CreateSpaceMaintenanceRecordAction;
use App\Actions\Tickets\CreateTicketAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Spaces\StoreSpaceMaintenanceTicketRequest;
use App\Http\Requests\Spaces\StoreSpaceRequest;
use App\Http\Requests\Spaces\UpdateSpaceRequest;
use App\Models\Space;
use App\Models\User;
use App\Services\Notifications\NotificationService;
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
                ->with([
                    'event:id,title,event_type,status,start_at,end_at,space_id',
                    'tasks:id,space_reservation_id,title,status,priority,due_date,assigned_to',
                    'tasks.assignee:id,name',
                ])
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
            'currentState' => $this->currentStateForSpace($space),
            'can' => [
                'reserve' => request()->user()->can('create', \App\Models\SpaceReservation::class),
                'createMaintenanceTicket' => request()->user()->can('spaces.create-maintenance-ticket')
                    || request()->user()->can('spaces.manage_maintenance')
                    || request()->user()->can('tickets.create'),
            ],
            'statuses' => Space::STATUSES,
        ]);
    }

    public function storeMaintenanceTicket(
        StoreSpaceMaintenanceTicketRequest $request,
        Space $space,
        CreateTicketAction $createTicketAction,
        CreateSpaceMaintenanceRecordAction $createSpaceMaintenanceRecordAction,
        NotificationService $notificationService,
    ): RedirectResponse {
        OrganizationScope::ensureModelBelongsToUserOrganization($space, $request->user());

        $payload = $request->validated();

        $ticket = $createTicketAction->execute($request->user(), [
            'organization_id' => $space->organization_id,
            'department_id' => $payload['department_id'] ?? null,
            'service_area_id' => $payload['service_area_id'] ?? null,
            'priority' => $payload['priority'] ?? 'normal',
            'status' => 'novo',
            'title' => $payload['title'],
            'description' => $payload['description'],
            'location_text' => $space->location_text,
            'source' => 'internal',
            'type' => 'maintenance',
            'category' => 'spaces',
            'subcategory' => 'space_maintenance',
            'visibility' => 'internal',
        ]);

        $record = $createSpaceMaintenanceRecordAction->execute($request->user(), [
            'organization_id' => $space->organization_id,
            'space_id' => $space->id,
            'ticket_id' => $ticket->id,
            'type' => 'maintenance',
            'status' => 'pending',
            'title' => $payload['title'],
            'description' => $payload['description'],
            'notes' => 'Registo criado automaticamente a partir da ficha de espaço.',
        ]);

        $recipients = User::query()
            ->where('organization_id', $space->organization_id)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['admin_junta', 'administrativo', 'operacional', 'manutencao']))
            ->pluck('id')
            ->all();

        if ($recipients !== []) {
            $notificationService->createForUsers($recipients, [
                'organization_id' => $space->organization_id,
                'type' => 'space_maintenance_ticket_created',
                'title' => 'Novo pedido de manutenção de espaço',
                'message' => "Foi criado um pedido de manutenção para o espaço {$space->name}: {$ticket->reference}.",
                'notifiable' => $record,
                'action_url' => route('admin.space-maintenance.show', $record),
                'priority' => 'high',
                'created_by' => $request->user()->id,
            ]);
        }

        return to_route('admin.spaces.show', $space)->with('success', 'Pedido de manutenção criado com sucesso.');
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

    private function currentStateForSpace(Space $space): string
    {
        if ($space->status === 'maintenance') {
            return 'maintenance';
        }

        if (in_array($space->status, ['unavailable', 'inactive'], true) || ! $space->is_active) {
            return 'unavailable';
        }

        $isReservedNow = $space->reservations()
            ->where('status', 'approved')
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->exists();

        return $isReservedNow ? 'reserved' : 'free';
    }
}
