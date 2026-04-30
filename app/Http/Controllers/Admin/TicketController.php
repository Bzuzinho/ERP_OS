<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Tickets\AssignTicketAction;
use App\Actions\Tickets\CreateTicketAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\StoreTicketRequest;
use App\Http\Requests\Tickets\UpdateTicketRequest;
use App\Models\Contact;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Ticket::class);

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $priority = $request->string('priority')->toString();
        $source = $request->string('source')->toString();

        $tickets = Ticket::query()
            ->with(['assignee:id,name', 'creator:id,name'])
            ->when($search, fn ($query) => $query
                ->where('reference', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%"))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($priority, fn ($query) => $query->where('priority', $priority))
            ->when($source, fn ($query) => $query->where('source', $source))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => compact('search', 'status', 'priority', 'source'),
            'statuses' => Ticket::STATUSES,
            'priorities' => Ticket::PRIORITIES,
            'sources' => Ticket::SOURCES,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Ticket::class);

        return Inertia::render('Admin/Tickets/Create', [
            'contacts' => Contact::query()->select('id', 'name')->orderBy('name')->get(),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'statuses' => Ticket::STATUSES,
            'priorities' => Ticket::PRIORITIES,
            'sources' => Ticket::SOURCES,
        ]);
    }

    public function store(StoreTicketRequest $request, CreateTicketAction $createTicketAction): RedirectResponse
    {
        $validated = $request->validated();

        $ticket = $createTicketAction->execute($request->user(), [
            ...$validated,
            'status' => $validated['status'] ?? 'novo',
        ]);

        return to_route('admin.tickets.show', $ticket)->with('success', 'Pedido criado com sucesso.');
    }

    public function show(Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'organization:id,name',
            'creator:id,name',
            'assignee:id,name',
            'contact:id,name,email,phone,mobile',
            'statusHistories.changedBy:id,name',
            'comments.user:id,name',
            'attachments.uploader:id,name',
            'activityLogs.user:id,name',
        ]);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => $ticket,
            'statuses' => Ticket::STATUSES,
            'priorities' => Ticket::PRIORITIES,
            'sources' => Ticket::SOURCES,
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function edit(Ticket $ticket): Response
    {
        $this->authorize('update', $ticket);

        return Inertia::render('Admin/Tickets/Edit', [
            'ticket' => $ticket,
            'contacts' => Contact::query()->select('id', 'name')->orderBy('name')->get(),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'priorities' => Ticket::PRIORITIES,
            'sources' => Ticket::SOURCES,
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket, ActivityLogger $activityLogger): RedirectResponse
    {
        $oldValues = $ticket->only([
            'contact_id',
            'assigned_to',
            'category',
            'subcategory',
            'priority',
            'title',
            'description',
            'location_text',
            'source',
            'visibility',
            'due_date',
        ]);

        $ticket->update($request->validated());

        $activityLogger->log(
            subject: $ticket,
            action: 'ticket.updated',
            user: $request->user(),
            organization: $ticket->organization,
            oldValues: $oldValues,
            newValues: $ticket->only(array_keys($oldValues)),
            description: 'Dados do pedido atualizados.',
        );

        return to_route('admin.tickets.show', $ticket)->with('success', 'Pedido atualizado com sucesso.');
    }

    public function assign(Request $request, Ticket $ticket, AssignTicketAction $assignTicketAction): RedirectResponse
    {
        $this->authorize('assign', $ticket);

        $data = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $assignTicketAction->execute($ticket, $data['assigned_to'] ?? null, $request->user());

        return to_route('admin.tickets.show', $ticket)->with('success', 'Responsavel atualizado com sucesso.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return to_route('admin.tickets.index')->with('success', 'Pedido eliminado com sucesso.');
    }
}
