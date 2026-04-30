<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\MeetingMinute;
use Inertia\Inertia;
use Inertia\Response;

class MeetingMinuteController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', MeetingMinute::class);

        $user = request()->user();

        $meetingMinutes = MeetingMinute::query()
            ->with(['event:id,title,start_at', 'document:id,title,visibility,status'])
            ->where('status', 'approved')
            ->latest('approved_at')
            ->get()
            ->filter(function (MeetingMinute $meetingMinute) use ($user) {
                if (! $user->can('view', $meetingMinute)) {
                    return false;
                }

                return ! $meetingMinute->document || $user->can('view', $meetingMinute->document);
            })
            ->values();

        return Inertia::render('Portal/MeetingMinutes/Index', [
            'meetingMinutes' => [
                'data' => $meetingMinutes,
            ],
        ]);
    }

    public function show(MeetingMinute $meetingMinute): Response
    {
        $this->authorize('view', $meetingMinute);

        $meetingMinute->load(['event:id,title,start_at,end_at', 'document.type:id,name']);

        return Inertia::render('Portal/MeetingMinutes/Show', [
            'meetingMinute' => $meetingMinute,
            'can' => [
                'download' => $meetingMinute->document ? request()->user()->can('download', $meetingMinute->document) : false,
            ],
        ]);
    }
}
