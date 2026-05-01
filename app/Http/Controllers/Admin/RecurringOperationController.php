<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Planning\CreateRecurringOperationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\StoreRecurringOperationRequest;
use App\Http\Requests\Planning\UpdateRecurringOperationRequest;
use App\Models\Department;
use App\Models\RecurringOperation;
use App\Models\Space;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecurringOperationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', RecurringOperation::class);

        $filters = $request->only(['status', 'frequency', 'operation_type', 'owner_user_id', 'department_id']);

        $operations = RecurringOperation::query()
            ->with(['owner:id,name', 'department:id,name', 'team:id,name'])
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['frequency'] ?? null, fn ($query, $value) => $query->where('frequency', $value))
            ->when($filters['operation_type'] ?? null, fn ($query, $value) => $query->where('operation_type', $value))
            ->when($filters['owner_user_id'] ?? null, fn ($query, $value) => $query->where('owner_user_id', $value))
            ->when($filters['department_id'] ?? null, fn ($query, $value) => $query->where('department_id', $value))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/RecurringOperations/Index', [
            'operations' => $operations,
            'filters' => $filters,
            'statuses' => RecurringOperation::STATUSES,
            'frequencies' => RecurringOperation::FREQUENCIES,
            'types' => RecurringOperation::TYPES,
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'departments' => Department::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', RecurringOperation::class);

        return Inertia::render('Admin/RecurringOperations/Create', [
            'statuses' => RecurringOperation::STATUSES,
            'frequencies' => RecurringOperation::FREQUENCIES,
            'types' => RecurringOperation::TYPES,
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'departments' => Department::query()->select('id', 'name')->orderBy('name')->get(),
            'teams' => Team::query()->select('id', 'name')->orderBy('name')->get(),
            'spaces' => Space::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreRecurringOperationRequest $request, CreateRecurringOperationAction $action): RedirectResponse
    {
        $operation = $action->execute($request->user(), $request->validated());

        return to_route('admin.recurring-operations.show', $operation)->with('success', 'Operacao recorrente criada com sucesso.');
    }

    public function show(RecurringOperation $recurringOperation): Response
    {
        $this->authorize('view', $recurringOperation);

        $recurringOperation->load(['owner:id,name', 'department:id,name', 'team:id,name', 'relatedSpace:id,name', 'runs.generatedTask:id,title,status', 'runs.generatedEvent:id,title,status']);

        return Inertia::render('Admin/RecurringOperations/Show', [
            'operation' => $recurringOperation,
            'statuses' => RecurringOperation::STATUSES,
            'frequencies' => RecurringOperation::FREQUENCIES,
            'types' => RecurringOperation::TYPES,
        ]);
    }

    public function edit(RecurringOperation $recurringOperation): Response
    {
        $this->authorize('update', $recurringOperation);

        return Inertia::render('Admin/RecurringOperations/Edit', [
            'operation' => $recurringOperation,
            'statuses' => RecurringOperation::STATUSES,
            'frequencies' => RecurringOperation::FREQUENCIES,
            'types' => RecurringOperation::TYPES,
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'departments' => Department::query()->select('id', 'name')->orderBy('name')->get(),
            'teams' => Team::query()->select('id', 'name')->orderBy('name')->get(),
            'spaces' => Space::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateRecurringOperationRequest $request, RecurringOperation $recurringOperation): RedirectResponse
    {
        $this->authorize('update', $recurringOperation);

        $recurringOperation->update($request->validated());

        return to_route('admin.recurring-operations.show', $recurringOperation)->with('success', 'Operacao recorrente atualizada com sucesso.');
    }

    public function destroy(RecurringOperation $recurringOperation): RedirectResponse
    {
        $this->authorize('delete', $recurringOperation);

        $recurringOperation->delete();

        return to_route('admin.recurring-operations.index')->with('success', 'Operacao recorrente removida com sucesso.');
    }
}
