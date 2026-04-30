<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Tickets\UpdateTicketStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\UpdateTicketStatusRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;

class TicketStatusController extends Controller
{
    public function update(
        UpdateTicketStatusRequest $request,
        Ticket $ticket,
        UpdateTicketStatusAction $updateTicketStatusAction,
    ): RedirectResponse {
        $updateTicketStatusAction->execute(
            ticket: $ticket,
            newStatus: $request->validated('status'),
            changedBy: $request->user(),
            notes: $request->validated('notes'),
        );

        return to_route('admin.tickets.show', $ticket)->with('success', 'Estado atualizado com sucesso.');
    }
}
