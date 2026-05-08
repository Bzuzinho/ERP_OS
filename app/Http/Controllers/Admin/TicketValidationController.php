<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Tickets\CancelTicketAction;
use App\Actions\Tickets\SubmitTicketForValidationAction;
use App\Actions\Tickets\ValidateTicketAction;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\Scopes\UserOperationalScopeService;
use Illuminate\Http\RedirectResponse;

class TicketValidationController extends Controller
{
    public function submitForValidation(
        Ticket $ticket,
        SubmitTicketForValidationAction $submitTicketForValidationAction,
        UserOperationalScopeService $scopeService,
    ): RedirectResponse {
        $this->authorize('submitForValidation', $ticket);

        $scopeService->validateContext(
            request()->user(),
            $ticket->organization_id,
            $ticket->department_id,
            $ticket->service_area_id,
        );

        $data = request()->validate([
            'notes' => ['nullable', 'string'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $submitTicketForValidationAction->execute(
            ticket: $ticket,
            actor: request()->user(),
            resolutionNotes: $data['resolution_notes'] ?? null,
            notes: $data['notes'] ?? null,
        );

        return to_route('admin.tickets.show', $ticket)->with('success', 'Pedido enviado para validação com sucesso.');
    }

    public function validate(
        Ticket $ticket,
        ValidateTicketAction $validateTicketAction,
        UserOperationalScopeService $scopeService,
    ): RedirectResponse {
        $this->authorize('validate', $ticket);

        $scopeService->validateContext(
            request()->user(),
            $ticket->organization_id,
            $ticket->department_id,
            $ticket->service_area_id,
        );

        $data = request()->validate([
            'validation_notes' => ['nullable', 'string'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $validateTicketAction->execute(
            ticket: $ticket,
            validator: request()->user(),
            notes: $data['validation_notes'] ?? null,
            resolutionNotes: $data['resolution_notes'] ?? null,
        );

        return to_route('admin.tickets.show', $ticket)->with('success', 'Pedido validado com sucesso.');
    }

    public function cancel(
        Ticket $ticket,
        CancelTicketAction $cancelTicketAction,
        UserOperationalScopeService $scopeService,
    ): RedirectResponse {
        $this->authorize('cancel', $ticket);

        $scopeService->validateContext(
            request()->user(),
            $ticket->organization_id,
            $ticket->department_id,
            $ticket->service_area_id,
        );

        $data = request()->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $cancelTicketAction->execute(
            ticket: $ticket,
            actor: request()->user(),
            notes: $data['notes'] ?? null,
        );

        return to_route('admin.tickets.show', $ticket)->with('success', 'Pedido cancelado com sucesso.');
    }
}
