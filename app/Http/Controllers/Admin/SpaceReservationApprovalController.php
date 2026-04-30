<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Spaces\ApproveSpaceReservationAction;
use App\Actions\Spaces\CompleteSpaceReservationAction;
use App\Actions\Spaces\RejectSpaceReservationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Spaces\ApproveSpaceReservationRequest;
use App\Http\Requests\Spaces\CompleteSpaceReservationRequest;
use App\Http\Requests\Spaces\RejectSpaceReservationRequest;
use App\Models\SpaceReservation;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class SpaceReservationApprovalController extends Controller
{
    public function approve(ApproveSpaceReservationRequest $request, SpaceReservation $spaceReservation, ApproveSpaceReservationAction $action): RedirectResponse
    {
        try {
            $action->execute($spaceReservation, $request->user(), $request->validated('notes'));
        } catch (RuntimeException $exception) {
            return back()->withErrors(['reservation' => $exception->getMessage()]);
        }

        return back()->with('success', 'Reserva aprovada com sucesso.');
    }

    public function reject(RejectSpaceReservationRequest $request, SpaceReservation $spaceReservation, RejectSpaceReservationAction $action): RedirectResponse
    {
        $action->execute(
            $spaceReservation,
            $request->user(),
            $request->validated('rejection_reason'),
            $request->validated('notes'),
        );

        return back()->with('success', 'Reserva rejeitada com sucesso.');
    }

    public function complete(CompleteSpaceReservationRequest $request, SpaceReservation $spaceReservation, CompleteSpaceReservationAction $action): RedirectResponse
    {
        $action->execute($spaceReservation, $request->user(), $request->validated('notes'));

        return back()->with('success', 'Reserva concluida com sucesso.');
    }
}
