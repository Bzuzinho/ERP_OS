<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Tickets\AssignTicketAction;
use App\Actions\Tickets\CreateTicketAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\StoreTicketRequest;
use App\Http\Requests\Tickets\UpdateTicketRequest;
use App\Models\Contact;
use App\Models\Department;
use App\Models\ServiceArea;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Notifications\TicketNotificationService;
use App\Services\Scopes\UserOperationalScopeService;
use App\Services\Tickets\TicketResolutionService;
use App\Services\Tickets\ActivityLogger;
use App\Support\OrganizationScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request, UserOperationalScopeService $scopeService): Response
    {
        $this->authorize('viewAny', Ticket::class);

        $user = $request->user();
        $allMyScopes = $request->boolean('all_my_scopes');

        $context = $scopeService->validateContext(
            $user,
            $allMyScopes
                ? null
                : ($request->integer('organization_id') ?: $user->defaultOrganization()?->id ?: $user->organization_id),
            $request->integer('department_id') ?: null,
            $request->integer('service_area_id') ?: null,
        );

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $priority = $request->string('priority')->toString();
        $source = $request->string('source')->toString();
        $type = $request->string('type')->toString();

        $tickets = Ticket::query()
            ->operational($user, $scopeService)
            ->byOperationalContext(
                $context->allMyScopes ? null : $context->organizationId,
                $context->departmentId,
                $context->serviceAreaId,
            )
            ->with([
                'assignee:id,name',
                'creator:id,name',
                'organization:id,name',
                'department:id,name',
                'serviceArea:id,name',
            ])
            ->when($search, fn ($query) => $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('reference', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            }))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($priority, fn ($query) => $query->where('priority', $priority))
            ->when($source, fn ($query) => $query->where('source', $source))
            ->byType($type)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $options = $this->buildOperationalOptions($user, $scopeService);

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'priority' => $priority,
                'source' => $source,
                'type' => $type,
                'organization_id' => $context->organizationId,
                'department_id' => $context->departmentId,
                'service_area_id' => $context->serviceAreaId,
                'all_my_scopes' => $context->allMyScopes,
            ],
            'statuses' => Ticket::STATUSES,
            'types' => Ticket::TYPES,
            'priorities' => Ticket::PRIORITIES,
            'sources' => Ticket::SOURCES,
            ...$options,
        ]);
    }

    public function create(Request $request, UserOperationalScopeService $scopeService): Response
    {
        $this->authorize('create', Ticket::class);

        $user = $request->user();
        $allMyScopes = $request->boolean('all_my_scopes');

        $context = $scopeService->validateContext(
            $user,
            $allMyScopes
                ? null
                : ($request->integer('organization_id') ?: $user->defaultOrganization()?->id ?: $user->organization_id),
            $request->integer('department_id') ?: null,
            $request->integer('service_area_id') ?: null,
        );

        $options = $this->buildOperationalOptions($user, $scopeService);

        return Inertia::render('Admin/Tickets/Create', [
            'contacts' => Contact::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
            'users' => User::query()->select('id', 'name', 'organization_id')->orderBy('name')->get(),
            'departments' => $options['departments'],
            'teams' => Team::query()->select('id', 'name', 'organization_id', 'department_id')->orderBy('name')->get(),
            'serviceAreas' => $options['serviceAreas'],
            'organizations' => $options['organizations'],
            'statuses' => Ticket::STATUSES,
            'types' => Ticket::TYPES,
            'priorities' => Ticket::PRIORITIES,
            'sources' => Ticket::SOURCES,
            'operationalContext' => [
                'organization_id' => $context->organizationId,
                'department_id' => $context->departmentId,
                'service_area_id' => $context->serviceAreaId,
                'all_my_scopes' => $context->allMyScopes,
            ],
        ]);
    }

    public function store(
        StoreTicketRequest $request,
        CreateTicketAction $createTicketAction,
        TicketNotificationService $ticketNotificationService,
        UserOperationalScopeService $scopeService,
    ): RedirectResponse
    {
        $validated = $request->validated();
        $allMyScopes = (bool) ($validated['all_my_scopes'] ?? false);

        if ($allMyScopes && empty($validated['organization_id'])) {
            throw ValidationException::withMessages([
                'organization_id' => 'No contexto all_my_scopes a organização é obrigatória para criar pedidos.',
            ]);
        }

        $context = $scopeService->validateContext(
            $request->user(),
            $validated['organization_id']
                ?? ($allMyScopes
                    ? null
                    : ($request->user()->defaultOrganization()?->id ?? $request->user()->organization_id)),
            $validated['department_id'] ?? null,
            $validated['service_area_id'] ?? null,
        );

        if ($context->allMyScopes) {
            throw ValidationException::withMessages([
                'organization_id' => 'Selecione uma organização válida para criar o pedido.',
            ]);
        }

        $this->validateScopedForeignKeys($validated, (int) $context->organizationId);

        unset($validated['all_my_scopes']);

        $ticket = $createTicketAction->execute($request->user(), [
            ...$validated,
            'status' => $validated['status'] ?? 'novo',
            'type' => $validated['type'] ?? 'internal',
            'organization_id' => $context->organizationId,
            'department_id' => $context->departmentId,
            'service_area_id' => $context->serviceAreaId,
        ]);

        try {
            $ticketNotificationService->notifyTicketCreated($ticket, $request->user());
        } catch (\Throwable $exception) {
            report($exception);
        }

        return to_route('admin.tickets.show', $ticket)->with('success', 'Pedido criado com sucesso.');
    }

    public function show(Ticket $ticket, TicketResolutionService $ticketResolutionService): Response
    {
        $this->authorize('view', $ticket);

        $this->ensureTicketInOperationalScope(request()->user(), $ticket, app(UserOperationalScopeService::class));

        $ticket->load([
            'organization:id,name',
            'creator:id,name',
            'assignee:id,name',
            'validator:id,name',
            'serviceArea:id,name',
            'team:id,name',
            'department:id,name',
            'contact:id,name,email,phone,mobile',
            'statusHistories.changedBy:id,name',
            'comments.user:id,name',
            'attachments.uploader:id,name',
            'activityLogs.user:id,name',
            'tasks:id,ticket_id,title,status',
        ]);

        $taskProgress = $ticketResolutionService->progress($ticket);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => $ticket,
            'statuses' => Ticket::STATUSES,
            'types' => Ticket::TYPES,
            'priorities' => Ticket::PRIORITIES,
            'sources' => Ticket::SOURCES,
            'users' => OrganizationScope::apply(User::query(), request()->user())->select('id', 'name')->orderBy('name')->get(),
            'can' => [
                'submitValidation' => request()->user()->can('submitForValidation', $ticket),
                'validate' => request()->user()->can('validate', $ticket),
                'cancel' => request()->user()->can('cancel', $ticket),
                'generateTasks' => request()->user()->can('generateTasks', $ticket),
            ],
            'ticketAbilities' => [
                'can_be_validated' => $ticket->canBeValidated(),
                'can_be_cancelled' => $ticket->canBeCancelled(),
                'can_generate_tasks' => $ticket->canGenerateTasks(),
                'can_submit_for_validation' => $ticketResolutionService->canSubmitForValidation($ticket),
            ],
            'taskProgress' => $taskProgress,
        ]);
    }

    public function edit(Ticket $ticket, UserOperationalScopeService $scopeService): Response
    {
        $this->authorize('update', $ticket);

        $user = request()->user();
        $this->ensureTicketInOperationalScope($user, $ticket, $scopeService);
        $options = $this->buildOperationalOptions($user, $scopeService);

        return Inertia::render('Admin/Tickets/Edit', [
            'ticket' => $ticket,
            'contacts' => Contact::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
            'users' => User::query()->select('id', 'name', 'organization_id')->orderBy('name')->get(),
            'departments' => $options['departments'],
            'teams' => Team::query()->select('id', 'name', 'organization_id', 'department_id')->orderBy('name')->get(),
            'serviceAreas' => $options['serviceAreas'],
            'organizations' => $options['organizations'],
            'types' => Ticket::TYPES,
            'priorities' => Ticket::PRIORITIES,
            'sources' => Ticket::SOURCES,
            'operationalContext' => [
                'organization_id' => $ticket->organization_id,
                'department_id' => $ticket->department_id,
                'service_area_id' => $ticket->service_area_id,
                'all_my_scopes' => false,
            ],
        ]);
    }

    public function update(
        UpdateTicketRequest $request,
        Ticket $ticket,
        ActivityLogger $activityLogger,
        UserOperationalScopeService $scopeService,
    ): RedirectResponse
    {
        $this->ensureTicketInOperationalScope($request->user(), $ticket, $scopeService);

        $validated = $request->validated();
        $allMyScopes = (bool) ($validated['all_my_scopes'] ?? false);

        $organizationId = $validated['organization_id'] ?? $ticket->organization_id;
        $departmentId = array_key_exists('department_id', $validated) ? $validated['department_id'] : $ticket->department_id;
        $serviceAreaId = array_key_exists('service_area_id', $validated) ? $validated['service_area_id'] : $ticket->service_area_id;

        if ($allMyScopes && empty($organizationId)) {
            throw ValidationException::withMessages([
                'organization_id' => 'No contexto all_my_scopes a organização é obrigatória.',
            ]);
        }

        $context = $scopeService->validateContext(
            $request->user(),
            $organizationId,
            $departmentId,
            $serviceAreaId,
        );

        $this->validateScopedForeignKeys($validated, (int) $context->organizationId);

        $oldValues = $ticket->only([
            'organization_id',
            'contact_id',
            'assigned_to',
            'department_id',
            'service_area_id',
            'team_id',
            'type',
            'category',
            'subcategory',
            'priority',
            'title',
            'description',
            'location_text',
            'source',
            'visibility',
            'due_date',
        ]);

        unset($validated['all_my_scopes']);

        $ticket->update([
            ...$validated,
            'organization_id' => $context->organizationId,
            'department_id' => $context->departmentId,
            'service_area_id' => $context->serviceAreaId,
            'type' => $validated['type'] ?? $ticket->type ?? 'internal',
        ]);

        $activityLogger->log(
            subject: $ticket,
            action: 'ticket.updated',
            user: $request->user(),
            organization: $ticket->organization,
            oldValues: $oldValues,
            newValues: $ticket->only(array_keys($oldValues)),
            description: 'Dados do pedido atualizados.',
        );

        return to_route('admin.tickets.show', $ticket)->with('success', 'Pedido atualizado com sucesso.');
    }

    public function assign(
        Request $request,
        Ticket $ticket,
        AssignTicketAction $assignTicketAction,
        TicketNotificationService $ticketNotificationService,
        UserOperationalScopeService $scopeService,
    ): RedirectResponse
    {
        $this->authorize('assign', $ticket);
        $this->ensureTicketInOperationalScope($request->user(), $ticket, $scopeService);

        $data = $request->validate([
            'assigned_to' => ['nullable', OrganizationScope::existsRuleForUser('users', $request->user(), organizationId: $ticket->organization_id)],
        ]);

        $assignTicketAction->execute($ticket, $data['assigned_to'] ?? null, $request->user());

        try {
            $ticketNotificationService->notifyTicketAssigned($ticket->fresh(), $request->user());
        } catch (\Throwable $exception) {
            report($exception);
        }

        return to_route('admin.tickets.show', $ticket)->with('success', 'Responsavel atualizado com sucesso.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $this->ensureTicketInOperationalScope(request()->user(), $ticket, app(UserOperationalScopeService::class));

        $ticket->delete();

        return to_route('admin.tickets.index')->with('success', 'Pedido eliminado com sucesso.');
    }

    private function ensureTicketInOperationalScope(User $user, Ticket $ticket, UserOperationalScopeService $scopeService): void
    {
        $scopeService->validateContext(
            $user,
            $ticket->organization_id,
            $ticket->department_id,
            $ticket->service_area_id,
        );
    }

    private function buildOperationalOptions(User $user, UserOperationalScopeService $scopeService): array
    {
        $organizations = $scopeService->organizationsFor($user)->values();

        $departments = new Collection();
        $serviceAreas = new Collection();

        foreach ($organizations as $organization) {
            $orgDepartments = $scopeService->departmentsFor($user, $organization);
            foreach ($orgDepartments as $department) {
                $departments->push([
                    'id' => $department->id,
                    'name' => $department->name,
                    'organization_id' => $organization->id,
                ]);
            }

            $orgServiceAreas = $scopeService->serviceAreasFor($user, $organization);
            foreach ($orgServiceAreas as $serviceArea) {
                $serviceAreas->push([
                    'id' => $serviceArea->id,
                    'name' => $serviceArea->name,
                    'organization_id' => $organization->id,
                    'department_id' => $serviceArea->department_id,
                ]);
            }
        }

        return [
            'organizations' => $organizations->map(fn ($organization) => [
                'id' => $organization->id,
                'name' => $organization->name,
            ])->values(),
            'departments' => $departments->sortBy('name')->values(),
            'serviceAreas' => $serviceAreas->sortBy('name')->values(),
        ];
    }

    private function validateScopedForeignKeys(array $data, int $organizationId): void
    {
        $errors = [];

        if (! empty($data['contact_id'])) {
            $contactBelongs = Contact::query()
                ->whereKey($data['contact_id'])
                ->where('organization_id', $organizationId)
                ->exists();

            if (! $contactBelongs) {
                $errors['contact_id'] = 'Contacto inválido para a organização selecionada.';
            }
        }

        if (! empty($data['assigned_to'])) {
            $assigneeBelongs = User::query()
                ->whereKey($data['assigned_to'])
                ->where('organization_id', $organizationId)
                ->exists();

            if (! $assigneeBelongs) {
                $errors['assigned_to'] = 'Responsável inválido para a organização selecionada.';
            }
        }

        if (! empty($data['team_id'])) {
            $teamBelongs = Team::query()
                ->whereKey($data['team_id'])
                ->where('organization_id', $organizationId)
                ->exists();

            if (! $teamBelongs) {
                $errors['team_id'] = 'Equipa inválida para a organização selecionada.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
