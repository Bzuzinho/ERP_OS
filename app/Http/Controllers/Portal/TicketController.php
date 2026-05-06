<?php

namespace App\Http\Controllers\Portal;

use App\Actions\Tickets\CreateTicketAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\StoreTicketRequest;
use App\Models\Contact;
use App\Models\ServiceArea;
use App\Models\Ticket;
use App\Services\Notifications\TicketNotificationService;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Ticket::class);

        $user = $request->user();

        $tickets = Ticket::query()
            ->visibleToUser($user)
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
                ->visibleToUser($user)
                ->where('user_id', $user->id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
            'themes' => ServiceArea::query()
                ->visibleToUser($user)
                ->where('is_active', true)
                ->select('id', 'name', 'slug')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(
        StoreTicketRequest $request,
        CreateTicketAction $createTicketAction,
        TicketNotificationService $ticketNotificationService,
    ): RedirectResponse
    {
        $validated = $request->validated();

        $serviceAreaId = null;
        if (! empty($validated['category'])) {
            $serviceAreaId = ServiceArea::query()
                ->where('organization_id', $request->user()->organization_id)
                ->where('slug', Str::slug((string) $validated['category']))
                ->value('id');
        }

        $payload = [
            'contact_id' => $validated['contact_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'] ?? null,
            'subcategory' => $validated['subcategory'] ?? null,
            'location_text' => $validated['location_text'] ?? null,
            'priority' => 'normal',
            'source' => 'portal',
            'status' => 'novo',
            'visibility' => 'internal',
            'assigned_to' => null,
            'department_id' => null,
            'team_id' => null,
            'service_area_id' => $serviceAreaId,
            'due_date' => null,
        ];

        $ticket = $createTicketAction->execute($request->user(), $payload);

        try {
            $ticketNotificationService->notifyTicketCreated($ticket, $request->user());
        } catch (\Throwable $exception) {
            report($exception);
        }

        return to_route('portal.tickets.show', $ticket)->with('success', 'Pedido submetido com sucesso. Pode acompanhar o estado nesta pagina.');
    }

    public function show(Request $request, Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $user = $request->user();
        $isAdmin = $user->can('tickets.view');

        $ticket->load([
            'statusHistories:id,ticket_id,new_status,created_at',
            'comments' => fn ($query) => $query
                ->with('user:id,name')
                ->when(! $isAdmin, fn ($commentQuery) => $commentQuery->where('visibility', 'public')),
            'attachments' => fn ($query) => $query
                ->with('uploader:id,name')
                ->when(! $isAdmin, fn ($attachmentQuery) => $attachmentQuery->where('visibility', 'public')),
        ]);

        return Inertia::render('Portal/Tickets/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'reference' => $ticket->reference,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'status' => $ticket->status,
                'location_text' => $ticket->location_text,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
                'status_histories' => $ticket->statusHistories,
                'comments' => $ticket->comments,
                'attachments' => $ticket->attachments,
            ],
        ]);
    }
}
