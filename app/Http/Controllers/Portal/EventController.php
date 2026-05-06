<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\OrganizationScope;
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
            ->visibleToUser($user)
            ->where(function ($query) use ($user) {
                $query
                    ->where('visibility', 'public')
                    ->orWhereHas('relatedContact', fn ($contactQuery) => $contactQuery->where('user_id', $user->id))
                    ->orWhereHas('participants', fn ($participantQuery) => $participantQuery
                        ->where('user_id', $user->id)
                        ->orWhereHas('contact', fn ($contactQuery) => $contactQuery->where('user_id', $user->id)));
            })
            ->whereIn('visibility', ['public', 'restricted'])
            ->orderBy('start_at')
            ->paginate(12)
            ->through(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'status' => $event->status,
                'start_at' => $event->start_at,
                'end_at' => $event->end_at,
                'location_text' => $event->location_text,
            ]);

        return Inertia::render('Portal/Events/Index', [
            'events' => $events,
        ]);
    }

    public function show(Event $event): Response
    {
        OrganizationScope::ensureModelBelongsToUserOrganization($event, request()->user());
        $this->authorize('view', $event);

        return Inertia::render('Portal/Events/Show', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'status' => $event->status,
                'start_at' => $event->start_at,
                'end_at' => $event->end_at,
                'location_text' => $event->location_text,
            ],
        ]);
    }
}
