<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Planning\CreateOperationalPlanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\StoreOperationalPlanRequest;
use App\Http\Requests\Planning\UpdateOperationalPlanRequest;
use App\Models\Department;
use App\Models\OperationalPlan;
use App\Models\Space;
use App\Models\Task;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OperationalPlanController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('planning.view'), 403);
        $this->authorize('viewAny', OperationalPlan::class);

        $user = $request->user();

        $filters = $request->only(['status', 'plan_type', 'visibility', 'department_id', 'team_id', 'search', 'start_date', 'end_date']);

        $plans = OperationalPlan::query()
            ->visibleToUser($user)
            ->with(['owner:id,name', 'department:id,name', 'team:id,name'])
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['plan_type'] ?? null, fn ($query, $value) => $query->where('plan_type', $value))
            ->when($filters['visibility'] ?? null, fn ($query, $value) => $query->where('visibility', $value))
            ->when($filters['department_id'] ?? null, fn ($query, $value) => $query->where('department_id', $value))
            ->when($filters['team_id'] ?? null, fn ($query, $value) => $query->where('team_id', $value))
            ->when($filters['search'] ?? null, fn ($query, $value) => $query->where('title', 'like', "%{$value}%"))
            ->when($filters['start_date'] ?? null, fn ($query, $value) => $query->whereDate('start_date', '>=', $value))
            ->when($filters['end_date'] ?? null, fn ($query, $value) => $query->whereDate('end_date', '<=', $value))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/OperationalPlans/Index', [
            'plans' => $plans,
            'filters' => $filters,
            'statuses' => OperationalPlan::STATUSES,
            'types' => OperationalPlan::TYPES,
            'visibilities' => OperationalPlan::VISIBILITIES,
            'departments' => Department::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
            'teams' => Team::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', OperationalPlan::class);

        $user = request()->user();

        return Inertia::render('Admin/OperationalPlans/Create', [
            'types' => OperationalPlan::TYPES,
            'statuses' => OperationalPlan::STATUSES,
            'visibilities' => OperationalPlan::VISIBILITIES,
            'users' => OrganizationScope::apply(User::query(), $user)->select('id', 'name')->orderBy('name')->get(),
            'departments' => Department::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
            'teams' => Team::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
            'tickets' => Ticket::query()->visibleToUser($user)->select('id', 'reference', 'title')->latest()->limit(100)->get(),
            'spaces' => Space::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreOperationalPlanRequest $request, CreateOperationalPlanAction $action): RedirectResponse
    {
        $plan = $action->execute($request->user(), $request->validated());

        return to_route('admin.operational-plans.show', $plan)->with('success', 'Plano operacional criado com sucesso.');
    }

    public function show(OperationalPlan $operationalPlan): Response
    {
        abort_unless(request()->user()->can('planning.view'), 403);
        $this->authorize('viewAny', OperationalPlan::class);
        $this->authorize('view', $operationalPlan);

        $user = request()->user();
        OrganizationScope::ensureModelBelongsToUserOrganization($operationalPlan, $user);

        $operationalPlan->load([
            'owner:id,name',
            'department:id,name',
            'team:id,name',
            'relatedTicket:id,reference,title',
            'relatedSpace:id,name,location_text',
            'tasks:id,title,status,priority,due_date',
            'participants.user:id,name',
            'participants.employee:id,employee_number',
            'participants.team:id,name',
            'resources.inventoryItem:id,name,sku',
            'resources.space:id,name',
            'comments.user:id,name',
            'attachments.uploader:id,name',
            'documents:id,title,visibility,status,related_type,related_id',
        ]);

        return Inertia::render('Admin/OperationalPlans/Show', [
            'plan' => $operationalPlan,
            'statuses' => OperationalPlan::STATUSES,
            'types' => OperationalPlan::TYPES,
            'visibilities' => OperationalPlan::VISIBILITIES,
            'tasks' => OrganizationScope::apply(Task::query(), $user)->select('id', 'title', 'status')->latest()->limit(200)->get(),
        ]);
    }

    public function edit(OperationalPlan $operationalPlan): Response
    {
        $this->authorize('update', $operationalPlan);

        $user = request()->user();
        OrganizationScope::ensureModelBelongsToUserOrganization($operationalPlan, $user);

        return Inertia::render('Admin/OperationalPlans/Edit', [
            'plan' => $operationalPlan,
            'types' => OperationalPlan::TYPES,
            'statuses' => OperationalPlan::STATUSES,
            'visibilities' => OperationalPlan::VISIBILITIES,
            'users' => OrganizationScope::apply(User::query(), $user)->select('id', 'name')->orderBy('name')->get(),
            'departments' => Department::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
            'teams' => Team::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
            'tickets' => Ticket::query()->visibleToUser($user)->select('id', 'reference', 'title')->latest()->limit(100)->get(),
            'spaces' => Space::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateOperationalPlanRequest $request, OperationalPlan $operationalPlan): RedirectResponse
    {
        $this->authorize('update', $operationalPlan);

        OrganizationScope::ensureModelBelongsToUserOrganization($operationalPlan, $request->user());

        $operationalPlan->update($request->validated());

        return to_route('admin.operational-plans.show', $operationalPlan)->with('success', 'Plano operacional atualizado com sucesso.');
    }

    public function destroy(OperationalPlan $operationalPlan): RedirectResponse
    {
        $this->authorize('delete', $operationalPlan);

        OrganizationScope::ensureModelBelongsToUserOrganization($operationalPlan, request()->user());

        $operationalPlan->delete();

        return to_route('admin.operational-plans.index')->with('success', 'Plano operacional removido com sucesso.');
    }
}
