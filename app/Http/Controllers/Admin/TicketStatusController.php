<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Tickets\UpdateTicketStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\UpdateTicketStatusRequest;
use App\Models\Ticket;
use App\Services\Notifications\TicketNotificationService;
use Illuminate\Http\RedirectResponse;

class TicketStatusController extends Controller
{
    public function update(
        UpdateTicketStatusRequest $request,
        Ticket $ticket,
        UpdateTicketStatusAction $updateTicketStatusAction,
        TicketNotificationService $ticketNotificationService,
    ): RedirectResponse {
        $oldStatus = $ticket->status;

        $updateTicketStatusAction->execute(
            ticket: $ticket,
            newStatus: $request->validated('status'),
            changedBy: $request->user(),
            notes: $request->validated('notes'),
        );

        $newStatus = (string) $request->validated('status');

        if ($oldStatus !== $newStatus) {
            try {
                $ticketNotificationService->notifyTicketStatusChanged($ticket->fresh(), $oldStatus, $newStatus, $request->user());
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return to_route('admin.tickets.show', $ticket)->with('success', 'Estado atualizado com sucesso.');
    }
}
