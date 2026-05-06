<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Spaces\CreateSpaceReservationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Spaces\StoreSpaceReservationRequest;
use App\Http\Requests\Spaces\UpdateSpaceReservationRequest;
use App\Models\Contact;
use App\Models\Space;
use App\Models\SpaceReservation;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpaceReservationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SpaceReservation::class);

        $user = $request->user();

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $spaceId = $request->string('space_id')->toString();
        $date = $request->string('date')->toString();
        $contactId = $request->string('contact_id')->toString();

        $reservations = SpaceReservation::query()
            ->visibleToUser($user)
            ->with(['space:id,name', 'contact:id,name', 'requestedBy:id,name', 'event:id,title'])
            ->when($search, fn ($query) => $query->where('purpose', 'like', "%{$search}%"))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($spaceId, fn ($query) => $query->where('space_id', $spaceId))
            ->when($contactId, fn ($query) => $query->where('contact_id', $contactId))
            ->when($date, fn ($query) => $query->whereDate('start_at', $date))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/SpaceReservations/Index', [
            'reservations' => $reservations,
            'filters' => compact('search', 'status', 'spaceId', 'date', 'contactId'),
            'spaces' => Space::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
            'contacts' => Contact::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
            'statuses' => SpaceReservation::STATUSES,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', SpaceReservation::class);

        $user = request()->user();

        return Inertia::render('Admin/SpaceReservations/Create', [
            'spaces' => Space::query()->visibleToUser($user)->select('id', 'name', 'requires_approval', 'has_cleaning_required')->where('is_active', true)->orderBy('name')->get(),
            'contacts' => Contact::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
            'statuses' => SpaceReservation::STATUSES,
        ]);
    }

    public function store(StoreSpaceReservationRequest $request, CreateSpaceReservationAction $action): RedirectResponse
    {
        $reservation = $action->execute($request->user(), $request->validated());

        return to_route('admin.space-reservations.show', $reservation)->with('success', 'Reserva criada com sucesso.');
    }

    public function show(SpaceReservation $spaceReservation): Response
    {
        $this->authorize('view', $spaceReservation);

        OrganizationScope::ensureModelBelongsToUserOrganization($spaceReservation, request()->user());

        $spaceReservation->load([
            'space:id,name,location_text,requires_approval,has_cleaning_required',
            'requestedBy:id,name',
            'contact:id,name,email,phone,mobile',
            'event:id,title,event_type,status,start_at,end_at',
            'approvedBy:id,name',
            'rejectedBy:id,name',
            'cancelledBy:id,name',
            'approvals.decidedBy:id,name',
            'cleaningRecords.assignee:id,name',
            'comments.user:id,name',
            'attachments.uploader:id,name',
        ]);

        return Inertia::render('Admin/SpaceReservations/Show', [
            'reservation' => $spaceReservation,
            'statuses' => SpaceReservation::STATUSES,
            'can' => [
                'approve' => request()->user()->can('approve', $spaceReservation),
                'cancel' => request()->user()->can('cancel', $spaceReservation),
                'update' => request()->user()->can('update', $spaceReservation),
            ],
        ]);
    }

    public function edit(SpaceReservation $spaceReservation): Response
    {
        $this->authorize('update', $spaceReservation);

        $user = request()->user();
        OrganizationScope::ensureModelBelongsToUserOrganization($spaceReservation, $user);

        return Inertia::render('Admin/SpaceReservations/Edit', [
            'reservation' => $spaceReservation,
            'spaces' => Space::query()->visibleToUser($user)->select('id', 'name')->where('is_active', true)->orderBy('name')->get(),
            'contacts' => Contact::query()->visibleToUser($user)->select('id', 'name')->orderBy('name')->get(),
            'statuses' => SpaceReservation::STATUSES,
        ]);
    }

    public function update(UpdateSpaceReservationRequest $request, SpaceReservation $spaceReservation): RedirectResponse
    {
        OrganizationScope::ensureModelBelongsToUserOrganization($spaceReservation, $request->user());
        $spaceReservation->update($request->validated());

        return to_route('admin.space-reservations.show', $spaceReservation)->with('success', 'Reserva atualizada com sucesso.');
    }

    public function destroy(SpaceReservation $spaceReservation): RedirectResponse
    {
        $this->authorize('delete', $spaceReservation);

        OrganizationScope::ensureModelBelongsToUserOrganization($spaceReservation, request()->user());

        $spaceReservation->delete();

        return to_route('admin.space-reservations.index')->with('success', 'Reserva removida com sucesso.');
    }
}
