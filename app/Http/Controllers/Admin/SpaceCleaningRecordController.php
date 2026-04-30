<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Spaces\CompleteSpaceCleaningRecordAction;
use App\Actions\Spaces\CreateSpaceCleaningRecordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Spaces\CompleteSpaceCleaningRecordRequest;
use App\Http\Requests\Spaces\StoreSpaceCleaningRecordRequest;
use App\Http\Requests\Spaces\UpdateSpaceCleaningRecordRequest;
use App\Models\Space;
use App\Models\SpaceCleaningRecord;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpaceCleaningRecordController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SpaceCleaningRecord::class);

        $status = $request->string('status')->toString();
        $spaceId = $request->string('space_id')->toString();
        $reservationId = $request->string('space_reservation_id')->toString();
        $assignee = $request->string('assigned_to')->toString();

        $records = SpaceCleaningRecord::query()
            ->with(['space:id,name', 'reservation:id,purpose,start_at,end_at', 'assignee:id,name'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($spaceId, fn ($query) => $query->where('space_id', $spaceId))
            ->when($reservationId, fn ($query) => $query->where('space_reservation_id', $reservationId))
            ->when($assignee, fn ($query) => $query->where('assigned_to', $assignee))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/SpaceCleaning/Index', [
            'records' => $records,
            'filters' => compact('status', 'spaceId', 'reservationId', 'assignee'),
            'spaces' => Space::query()->select('id', 'name')->orderBy('name')->get(),
            'reservations' => SpaceReservation::query()->select('id', 'purpose')->latest()->limit(200)->get(),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'statuses' => SpaceCleaningRecord::STATUSES,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', SpaceCleaningRecord::class);

        return Inertia::render('Admin/SpaceCleaning/Create', [
            'spaces' => Space::query()->select('id', 'name')->orderBy('name')->get(),
            'reservations' => SpaceReservation::query()->select('id', 'purpose')->latest()->limit(200)->get(),
            'tasks' => Task::query()->select('id', 'title')->latest()->limit(100)->get(),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'statuses' => SpaceCleaningRecord::STATUSES,
        ]);
    }

    public function store(StoreSpaceCleaningRecordRequest $request, CreateSpaceCleaningRecordAction $action): RedirectResponse
    {
        $record = $action->execute($request->user(), [
            ...$request->validated(),
            'status' => $request->validated('status') ?? 'pending',
        ]);

        return to_route('admin.space-cleaning.show', $record)->with('success', 'Registo de limpeza criado com sucesso.');
    }

    public function show(SpaceCleaningRecord $spaceCleaning): Response
    {
        $this->authorize('view', $spaceCleaning);

        $spaceCleaning->load([
            'space:id,name',
            'reservation:id,purpose,start_at,end_at,status',
            'task:id,title,status',
            'assignee:id,name',
            'completedBy:id,name',
            'comments.user:id,name',
            'attachments.uploader:id,name',
        ]);

        return Inertia::render('Admin/SpaceCleaning/Show', [
            'record' => $spaceCleaning,
            'statuses' => SpaceCleaningRecord::STATUSES,
        ]);
    }

    public function edit(SpaceCleaningRecord $spaceCleaning): Response
    {
        $this->authorize('update', $spaceCleaning);

        return Inertia::render('Admin/SpaceCleaning/Edit', [
            'record' => $spaceCleaning,
            'spaces' => Space::query()->select('id', 'name')->orderBy('name')->get(),
            'reservations' => SpaceReservation::query()->select('id', 'purpose')->latest()->limit(200)->get(),
            'tasks' => Task::query()->select('id', 'title')->latest()->limit(100)->get(),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'statuses' => SpaceCleaningRecord::STATUSES,
        ]);
    }

    public function update(UpdateSpaceCleaningRecordRequest $request, SpaceCleaningRecord $spaceCleaning): RedirectResponse
    {
        $spaceCleaning->update($request->validated());

        return to_route('admin.space-cleaning.show', $spaceCleaning)->with('success', 'Registo de limpeza atualizado com sucesso.');
    }

    public function destroy(SpaceCleaningRecord $spaceCleaning): RedirectResponse
    {
        $this->authorize('delete', $spaceCleaning);

        $spaceCleaning->delete();

        return to_route('admin.space-cleaning.index')->with('success', 'Registo de limpeza removido com sucesso.');
    }

    public function complete(CompleteSpaceCleaningRecordRequest $request, SpaceCleaningRecord $spaceCleaning, CompleteSpaceCleaningRecordAction $action): RedirectResponse
    {
        $action->execute($spaceCleaning, $request->user());

        return back()->with('success', 'Limpeza concluida com sucesso.');
    }
}
