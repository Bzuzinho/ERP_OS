<?php

namespace App\Http\Controllers\Portal;

use App\Actions\Tickets\CreateTicketAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\StoreTicketRequest;
use App\Models\Contact;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Ticket::class);

        $user = $request->user();

        $tickets = Ticket::query()
            ->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('contact', fn ($contactQuery) => $contactQuery->where('user_id', $user->id));
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Portal/Tickets/Index', [
            'tickets' => $tickets,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Ticket::class);

        $user = $request->user();

        return Inertia::render('Portal/Tickets/Create', [
            'contacts' => Contact::query()
                ->where('user_id', $user->id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
            'priorities' => Ticket::PRIORITIES,
            'sources' => Ticket::SOURCES,
        ]);
    }

    public function store(StoreTicketRequest $request, CreateTicketAction $createTicketAction): RedirectResponse
    {
        $validated = $request->validated();

        $ticket = $createTicketAction->execute($request->user(), [
            ...$validated,
            'source' => $validated['source'] ?? 'portal',
            'status' => 'novo',
            'visibility' => 'internal',
            'assigned_to' => null,
        ]);

        return to_route('portal.tickets.show', $ticket)->with('success', 'Pedido submetido com sucesso.');
    }

    public function show(Request $request, Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $user = $request->user();
        $isAdmin = $user->can('tickets.view');

        $ticket->load([
            'statusHistories.changedBy:id,name',
            'comments' => fn ($query) => $query
                ->with('user:id,name')
                ->when(! $isAdmin, fn ($commentQuery) => $commentQuery->where('visibility', 'public')),
            'attachments' => fn ($query) => $query
                ->with('uploader:id,name')
                ->when(! $isAdmin, fn ($attachmentQuery) => $attachmentQuery->where('visibility', 'public')),
        ]);

        return Inertia::render('Portal/Tickets/Show', [
            'ticket' => $ticket,
            'statuses' => Ticket::STATUSES,
        ]);
    }
}
