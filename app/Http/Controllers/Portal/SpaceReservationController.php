<?php

namespace App\Http\Controllers\Portal;

use App\Actions\Spaces\CreateSpaceReservationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Spaces\StoreSpaceReservationRequest;
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
        $contactIds = $user->contacts()->pluck('id');

        $reservations = SpaceReservation::query()
            ->visibleToUser($user)
            ->with(['space:id,name'])
            ->where(function ($query) use ($user, $contactIds) {
                $query->where('requested_by_user_id', $user->id)
                    ->orWhereIn('contact_id', $contactIds);
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Portal/SpaceReservations/Index', [
            'reservations' => $reservations,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', SpaceReservation::class);

        $user = $request->user();

        return Inertia::render('Portal/SpaceReservations/Create', [
            'spaces' => Space::query()
                ->visibleToUser($user)
                ->where('is_active', true)
                ->where('is_public', true)
                ->select('id', 'name', 'requires_approval', 'status')
                ->orderBy('name')
                ->get(),
            'contacts' => Contact::query()->visibleToUser($user)->where('user_id', $user->id)->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreSpaceReservationRequest $request, CreateSpaceReservationAction $action): RedirectResponse
    {
        $reservation = $action->execute($request->user(), $request->validated(), true);

        return to_route('portal.space-reservations.show', $reservation)->with('success', 'Pedido de reserva submetido com sucesso.');
    }

    public function show(Request $request, SpaceReservation $spaceReservation): Response
    {
        $this->authorize('view', $spaceReservation);

        $spaceReservation->load(['space:id,name,location_text', 'contact:id,name', 'approvals.decidedBy:id,name']);

        return Inertia::render('Portal/SpaceReservations/Show', [
            'reservation' => [
                'id' => $spaceReservation->id,
                'status' => $spaceReservation->status,
                'start_at' => $spaceReservation->start_at,
                'end_at' => $spaceReservation->end_at,
                'purpose' => $spaceReservation->purpose,
                'notes' => $spaceReservation->notes,
                'space' => $spaceReservation->space,
                'contact' => $spaceReservation->contact,
                'approvals' => $spaceReservation->approvals->map(fn ($approval) => [
                    'id' => $approval->id,
                    'action' => $approval->action,
                    'new_status' => $approval->new_status,
                    'notes' => $approval->notes,
                    'created_at' => $approval->created_at,
                ])->values(),
            ],
            'canCancel' => $request->user()->can('cancel', $spaceReservation),
        ]);
    }
}
