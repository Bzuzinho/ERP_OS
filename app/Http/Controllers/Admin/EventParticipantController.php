<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreEventParticipantRequest;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\RedirectResponse;

class EventParticipantController extends Controller
{
    public function store(StoreEventParticipantRequest $request, Event $event): RedirectResponse
    {
        $event->participants()->create([
            ...$request->validated(),
            'attendance_status' => $request->validated('attendance_status') ?? 'invited',
        ]);

        return back()->with('success', 'Participante adicionado com sucesso.');
    }

    public function destroy(Event $event, EventParticipant $participant): RedirectResponse
    {
        $this->authorize('update', $event);

        abort_unless($participant->event_id === $event->id, 404);

        $participant->delete();

        return back()->with('success', 'Participante removido com sucesso.');
    }
}
