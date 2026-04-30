<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Event::class);

        $user = $request->user();

        $events = Event::query()
            ->where(function ($query) use ($user) {
                $query
                    ->where('visibility', 'public')
                    ->orWhereHas('relatedContact', fn ($contactQuery) => $contactQuery->where('user_id', $user->id))
                    ->orWhereHas('participants', fn ($participantQuery) => $participantQuery
                        ->where('user_id', $user->id)
                        ->orWhereHas('contact', fn ($contactQuery) => $contactQuery->where('user_id', $user->id)));
            })
            ->whereIn('visibility', ['public', 'restricted'])
            ->with(['relatedTicket:id,reference,title', 'relatedContact:id,name'])
            ->orderBy('start_at')
            ->paginate(12);

        return Inertia::render('Portal/Events/Index', [
            'events' => $events,
        ]);
    }

    public function show(Event $event): Response
    {
        $this->authorize('view', $event);

        $event->load(['relatedTicket:id,reference,title', 'relatedContact:id,name']);

        return Inertia::render('Portal/Events/Show', [
            'event' => $event,
        ]);
    }
}
