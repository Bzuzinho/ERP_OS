<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Events\CreateEventAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Event::class);

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $eventType = $request->string('event_type')->toString();
        $date = $request->string('date')->toString();

        $events = Event::query()
            ->with(['relatedTicket:id,reference,title', 'relatedContact:id,name'])
            ->when($search, fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('location_text', 'like', "%{$search}%"))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($eventType, fn ($query) => $query->where('event_type', $eventType))
            ->when($date, fn ($query) => $query->whereDate('start_at', $date))
            ->orderBy('start_at')
            ->paginate(15)
            ->withQueryString();

        $dayList = Event::query()
            ->with(['relatedTicket:id,reference,title', 'relatedContact:id,name'])
            ->whereDate('start_at', now()->toDateString())
            ->orderBy('start_at')
            ->get();

        return Inertia::render('Admin/Events/Index', [
            'events' => $events,
            'dayList' => $dayList,
            'filters' => compact('search', 'status', 'eventType', 'date'),
            'statuses' => Event::STATUSES,
            'eventTypes' => Event::TYPES,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Event::class);

        return Inertia::render('Admin/Events/Create', [
            'statuses' => Event::STATUSES,
            'eventTypes' => Event::TYPES,
            'visibilities' => Event::VISIBILITIES,
            'tickets' => Ticket::query()->select('id', 'reference', 'title')->latest()->limit(100)->get(),
            'contacts' => Contact::query()->select('id', 'name')->orderBy('name')->limit(200)->get(),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreEventRequest $request, CreateEventAction $createEventAction): RedirectResponse
    {
        $event = $createEventAction->execute($request->user(), [
            ...$request->validated(),
            'status' => $request->validated('status') ?? 'scheduled',
        ]);

        return to_route('admin.events.show', $event)->with('success', 'Evento criado com sucesso.');
    }

    public function show(Event $event): Response
    {
        $this->authorize('view', $event);

        $event->load([
            'creator:id,name',
            'relatedTicket:id,reference,title',
            'relatedContact:id,name,email,phone,mobile',
            'participants.user:id,name',
            'participants.contact:id,name',
            'comments.user:id,name',
            'attachments.uploader:id,name',
        ]);

        return Inertia::render('Admin/Events/Show', [
            'event' => $event,
            'statuses' => Event::STATUSES,
            'eventTypes' => Event::TYPES,
        ]);
    }

    public function edit(Event $event): Response
    {
        $this->authorize('update', $event);

        return Inertia::render('Admin/Events/Edit', [
            'event' => $event,
            'eventTypes' => Event::TYPES,
            'visibilities' => Event::VISIBILITIES,
            'tickets' => Ticket::query()->select('id', 'reference', 'title')->latest()->limit(100)->get(),
            'contacts' => Contact::query()->select('id', 'name')->orderBy('name')->limit(200)->get(),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $event->update($request->validated());

        return to_route('admin.events.show', $event)->with('success', 'Evento atualizado com sucesso.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return to_route('admin.events.index')->with('success', 'Evento removido com sucesso.');
    }
}
