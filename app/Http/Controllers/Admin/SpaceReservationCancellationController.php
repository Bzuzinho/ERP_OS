<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Spaces\CancelSpaceReservationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Spaces\CancelSpaceReservationRequest;
use App\Models\SpaceReservation;
use Illuminate\Http\RedirectResponse;

class SpaceReservationCancellationController extends Controller
{
    public function __invoke(CancelSpaceReservationRequest $request, SpaceReservation $spaceReservation, CancelSpaceReservationAction $action): RedirectResponse
    {
        $action->execute(
            $spaceReservation,
            $request->user(),
            $request->validated('cancellation_reason'),
            $request->validated('notes'),
        );

        return back()->with('success', 'Reserva cancelada com sucesso.');
    }
}
