<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Documents\CreateMeetingMinuteAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\MeetingMinutes\StoreMeetingMinuteRequest;
use App\Http\Requests\MeetingMinutes\UpdateMeetingMinuteRequest;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Event;
use App\Models\MeetingMinute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MeetingMinuteController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', MeetingMinute::class);

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $eventId = $request->string('event_id')->toString();
        $date = $request->string('date')->toString();

        $meetingMinutes = MeetingMinute::query()
            ->with(['event:id,title,start_at', 'approvedBy:id,name'])
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($eventId, fn ($query) => $query->where('event_id', $eventId))
            ->when($date, fn ($query) => $query->whereDate('created_at', $date))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/MeetingMinutes/Index', [
            'meetingMinutes' => $meetingMinutes,
            'filters' => compact('search', 'status', 'eventId', 'date'),
            'statuses' => MeetingMinute::STATUSES,
            'events' => Event::query()->select('id', 'title', 'start_at')->latest('start_at')->limit(100)->get(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', MeetingMinute::class);

        return Inertia::render('Admin/MeetingMinutes/Create', [
            'events' => Event::query()->select('id', 'title', 'start_at')->latest('start_at')->limit(200)->get(),
            'documents' => Document::query()->select('id', 'title')->latest()->limit(200)->get(),
            'documentTypes' => DocumentType::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreMeetingMinuteRequest $request, CreateMeetingMinuteAction $createMeetingMinuteAction): RedirectResponse
    {
        $meetingMinute = $createMeetingMinuteAction->execute(
            user: $request->user(),
            data: $request->validated(),
            file: $request->file('file'),
        );

        return to_route('admin.meeting-minutes.show', $meetingMinute)->with('success', 'Ata criada com sucesso.');
    }

    public function show(MeetingMinute $meetingMinute): Response
    {
        $this->authorize('view', $meetingMinute);

        $meetingMinute->load([
            'event:id,title,start_at,end_at',
            'document.type:id,name',
            'document.uploader:id,name',
            'creator:id,name',
            'approvedBy:id,name',
        ]);

        return Inertia::render('Admin/MeetingMinutes/Show', [
            'meetingMinute' => $meetingMinute,
            'can' => [
                'approve' => request()->user()->can('approve', $meetingMinute),
            ],
        ]);
    }

    public function edit(MeetingMinute $meetingMinute): Response
    {
        $this->authorize('update', $meetingMinute);

        return Inertia::render('Admin/MeetingMinutes/Edit', [
            'meetingMinute' => $meetingMinute,
            'statuses' => MeetingMinute::STATUSES,
            'events' => Event::query()->select('id', 'title', 'start_at')->latest('start_at')->limit(200)->get(),
            'documents' => Document::query()->select('id', 'title')->latest()->limit(200)->get(),
        ]);
    }

    public function update(UpdateMeetingMinuteRequest $request, MeetingMinute $meetingMinute): RedirectResponse
    {
        $meetingMinute->update($request->validated());

        return to_route('admin.meeting-minutes.show', $meetingMinute)->with('success', 'Ata atualizada com sucesso.');
    }

    public function destroy(MeetingMinute $meetingMinute): RedirectResponse
    {
        $this->authorize('delete', $meetingMinute);

        $meetingMinute->delete();

        return to_route('admin.meeting-minutes.index')->with('success', 'Ata removida com sucesso.');
    }
}
