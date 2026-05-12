<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\CreateResourceRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreResourceRequestRequest;
use App\Models\Event;
use App\Models\InventoryItem;
use App\Models\OperationalPlan;
use App\Models\ResourceRequest;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResourceRequestController extends Controller
{
    public function __construct(
        private readonly CreateResourceRequestAction $createAction,
    ) {
    }

    public function index(Request $request): Response
    {
        $this->authorize('resources.view');

        $user = $request->user();
        $status = $request->string('status')->toString();

        $requests = ResourceRequest::query()
            ->where('organization_id', $user->organization_id)
            ->with(['requestedBy:id,name', 'approvedBy:id,name', 'rejectedBy:id,name'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(15);

        return Inertia::render('Admin/ResourceRequests/Index', [
            'requests' => $requests,
            'statuses' => ResourceRequest::STATUSES,
            'filters' => ['status' => $status],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('resources.create');

        $user = $request->user();

        return Inertia::render('Admin/ResourceRequests/Create', [
            'organizationId' => (int) $user->organization_id,
            'inventoryItems' => InventoryItem::query()
                ->where('organization_id', $user->organization_id)
                ->where('is_active', true)
                ->select('id', 'name', 'item_type', 'is_stock_tracked', 'is_loanable', 'current_stock', 'status')
                ->orderBy('name')
                ->get(),
            'requestableOptions' => [
                'spaceReservation' => SpaceReservation::query()->where('organization_id', $user->organization_id)->select('id', 'purpose')->latest('id')->limit(20)->get(),
                'event' => Event::query()->where('organization_id', $user->organization_id)->select('id', 'title')->latest('id')->limit(20)->get(),
                'task' => Task::query()->where('organization_id', $user->organization_id)->select('id', 'title')->latest('id')->limit(20)->get(),
                'ticket' => Ticket::query()->where('organization_id', $user->organization_id)->select('id', 'title')->latest('id')->limit(20)->get(),
                'operationalPlan' => OperationalPlan::query()->where('organization_id', $user->organization_id)->select('id', 'title')->latest('id')->limit(20)->get(),
            ],
        ]);
    }

    public function store(StoreResourceRequestRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $payload['requestable_type'] = $this->resolveRequestableClass($payload['requestable_type'] ?? null);

        $resourceRequest = $this->createAction->execute($request->user(), $payload);

        return to_route('admin.resource-requests.show', $resourceRequest)
            ->with('success', 'Requisicao de recursos criada com sucesso.');
    }

    public function show(Request $request, ResourceRequest $resourceRequest): Response
    {
        $this->authorize('resources.view');

        $this->validateOrgAccess($resourceRequest);

        $resourceRequest->load([
            'requestedBy:id,name',
            'approvedBy:id,name',
            'rejectedBy:id,name',
            'requestable',
            'items.inventoryItem:id,name,item_type,is_stock_tracked,is_loanable,unit,current_stock',
        ]);

        return Inertia::render('Admin/ResourceRequests/Show', [
            'request' => $resourceRequest,
            'can' => [
                'approve' => $request->user()->can('resources.approve'),
                'deliver' => $request->user()->can('resources.deliver'),
                'return' => $request->user()->can('resources.return'),
                'manage' => $request->user()->can('resources.manage'),
            ],
        ]);
    }

    public function storeForSpaceReservation(StoreResourceRequestRequest $request, SpaceReservation $spaceReservation): RedirectResponse
    {
        abort_if((int) $spaceReservation->organization_id !== (int) $request->user()->organization_id, 403);

        return $this->storeWithRequestable($request, SpaceReservation::class, (int) $spaceReservation->id);
    }

    public function storeForEvent(StoreResourceRequestRequest $request, Event $event): RedirectResponse
    {
        abort_if((int) $event->organization_id !== (int) $request->user()->organization_id, 403);

        return $this->storeWithRequestable($request, Event::class, (int) $event->id);
    }

    public function storeForTask(StoreResourceRequestRequest $request, Task $task): RedirectResponse
    {
        abort_if((int) $task->organization_id !== (int) $request->user()->organization_id, 403);

        return $this->storeWithRequestable($request, Task::class, (int) $task->id);
    }

    public function storeForTicket(StoreResourceRequestRequest $request, Ticket $ticket): RedirectResponse
    {
        abort_if((int) $ticket->organization_id !== (int) $request->user()->organization_id, 403);

        return $this->storeWithRequestable($request, Ticket::class, (int) $ticket->id);
    }

    public function storeForOperationalPlan(StoreResourceRequestRequest $request, OperationalPlan $operationalPlan): RedirectResponse
    {
        abort_if((int) $operationalPlan->organization_id !== (int) $request->user()->organization_id, 403);

        return $this->storeWithRequestable($request, OperationalPlan::class, (int) $operationalPlan->id);
    }

    private function storeWithRequestable(StoreResourceRequestRequest $request, string $requestableType, int $requestableId): RedirectResponse
    {
        $payload = $request->validated();
        $payload['requestable_type'] = $requestableType;
        $payload['requestable_id'] = $requestableId;

        $resourceRequest = $this->createAction->execute($request->user(), $payload);

        return to_route('admin.resource-requests.show', $resourceRequest)
            ->with('success', 'Requisicao de recursos criada com sucesso.');
    }

    private function resolveRequestableClass(?string $requestableType): ?string
    {
        return match ($requestableType) {
            'space_reservation' => SpaceReservation::class,
            'event' => Event::class,
            'task' => Task::class,
            'ticket' => Ticket::class,
            'operational_plan' => OperationalPlan::class,
            default => null,
        };
    }

    private function validateOrgAccess(ResourceRequest $resourceRequest): void
    {
        if ($resourceRequest->organization_id !== auth()->user()->organization_id) {
            abort(403, 'Acesso nao autorizado.');
        }
    }
}
