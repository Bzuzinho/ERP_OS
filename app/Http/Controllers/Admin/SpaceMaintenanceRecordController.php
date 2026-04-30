<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Spaces\CreateSpaceMaintenanceRecordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Spaces\StoreSpaceMaintenanceRecordRequest;
use App\Http\Requests\Spaces\UpdateSpaceMaintenanceRecordRequest;
use App\Models\Space;
use App\Models\SpaceMaintenanceRecord;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpaceMaintenanceRecordController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SpaceMaintenanceRecord::class);

        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();
        $spaceId = $request->string('space_id')->toString();
        $assignee = $request->string('assigned_to')->toString();

        $records = SpaceMaintenanceRecord::query()
            ->with(['space:id,name', 'assignee:id,name'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($spaceId, fn ($query) => $query->where('space_id', $spaceId))
            ->when($assignee, fn ($query) => $query->where('assigned_to', $assignee))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/SpaceMaintenance/Index', [
            'records' => $records,
            'filters' => compact('status', 'type', 'spaceId', 'assignee'),
            'spaces' => Space::query()->select('id', 'name')->orderBy('name')->get(),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'statuses' => SpaceMaintenanceRecord::STATUSES,
            'types' => SpaceMaintenanceRecord::TYPES,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', SpaceMaintenanceRecord::class);

        return Inertia::render('Admin/SpaceMaintenance/Create', [
            'spaces' => Space::query()->select('id', 'name')->orderBy('name')->get(),
            'tickets' => Ticket::query()->select('id', 'reference', 'title')->latest()->limit(100)->get(),
            'tasks' => Task::query()->select('id', 'title')->latest()->limit(100)->get(),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'statuses' => SpaceMaintenanceRecord::STATUSES,
            'types' => SpaceMaintenanceRecord::TYPES,
        ]);
    }

    public function store(StoreSpaceMaintenanceRecordRequest $request, CreateSpaceMaintenanceRecordAction $action): RedirectResponse
    {
        $record = $action->execute($request->user(), [
            ...$request->validated(),
            'status' => $request->validated('status') ?? 'pending',
        ]);

        return to_route('admin.space-maintenance.show', $record)->with('success', 'Registo de manutencao criado com sucesso.');
    }

    public function show(SpaceMaintenanceRecord $spaceMaintenance): Response
    {
        $this->authorize('view', $spaceMaintenance);

        $spaceMaintenance->load([
            'space:id,name',
            'ticket:id,reference,title',
            'task:id,title,status',
            'assignee:id,name',
            'completedBy:id,name',
            'comments.user:id,name',
            'attachments.uploader:id,name',
        ]);

        return Inertia::render('Admin/SpaceMaintenance/Show', [
            'record' => $spaceMaintenance,
            'statuses' => SpaceMaintenanceRecord::STATUSES,
            'types' => SpaceMaintenanceRecord::TYPES,
        ]);
    }

    public function edit(SpaceMaintenanceRecord $spaceMaintenance): Response
    {
        $this->authorize('update', $spaceMaintenance);

        return Inertia::render('Admin/SpaceMaintenance/Edit', [
            'record' => $spaceMaintenance,
            'spaces' => Space::query()->select('id', 'name')->orderBy('name')->get(),
            'tickets' => Ticket::query()->select('id', 'reference', 'title')->latest()->limit(100)->get(),
            'tasks' => Task::query()->select('id', 'title')->latest()->limit(100)->get(),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'statuses' => SpaceMaintenanceRecord::STATUSES,
            'types' => SpaceMaintenanceRecord::TYPES,
        ]);
    }

    public function update(UpdateSpaceMaintenanceRecordRequest $request, SpaceMaintenanceRecord $spaceMaintenance): RedirectResponse
    {
        $spaceMaintenance->update($request->validated());

        return to_route('admin.space-maintenance.show', $spaceMaintenance)->with('success', 'Registo de manutencao atualizado com sucesso.');
    }

    public function destroy(SpaceMaintenanceRecord $spaceMaintenance): RedirectResponse
    {
        $this->authorize('delete', $spaceMaintenance);

        $spaceMaintenance->delete();

        return to_route('admin.space-maintenance.index')->with('success', 'Registo de manutencao removido com sucesso.');
    }
}
