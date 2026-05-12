<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyCalendarController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $events = Event::query()
            ->with(['space:id,name', 'createdBy:id,name', 'relatedTicket:id,reference,title'])
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_at')
            ->paginate(15);

        return Inertia::render('Mobile/Calendar/Index', [
            'events' => $events,
        ]);
    }
}
