<?php

namespace App\Services\Dashboard;

use App\Models\Document;
use App\Models\Event;
use App\Models\NotificationRecipient;
use App\Models\OperationalPlan;
use App\Models\SpaceReservation;
use App\Models\Ticket;
use App\Models\User;

class PortalDashboardService
{
    public function getData(User $user): array
    {
        $contactIds = $user->contacts()->pluck('id');

        $ticketsBase = Ticket::query()
            ->where('organization_id', $user->organization_id)
            ->where(function ($query) use ($user, $contactIds) {
                $query->whereIn('contact_id', $contactIds)
                    ->orWhere('created_by', $user->id);
            });

        $events = Event::query()
            ->where('organization_id', $user->organization_id)
            ->where('start_at', '>=', now())
            ->where(function ($query) use ($user, $contactIds) {
                $query->where('visibility', 'public')
                    ->orWhereHas('relatedContact', fn ($contactQuery) => $contactQuery->whereIn('id', $contactIds))
                    ->orWhereHas('participants', fn ($participantQuery) => $participantQuery
                        ->where('user_id', $user->id)
                        ->orWhereIn('contact_id', $contactIds));
            })
            ->orderBy('start_at')
            ->limit(8)
            ->get(['id', 'title', 'event_type', 'status', 'start_at', 'end_at', 'location_text']);

        $reservations = SpaceReservation::query()
            ->where('organization_id', $user->organization_id)
            ->where('start_at', '>=', now())
            ->where(function ($query) use ($user, $contactIds) {
                $query->where('requested_by_user_id', $user->id)
                    ->orWhereIn('contact_id', $contactIds);
            })
            ->with(['space:id,name'])
            ->orderBy('start_at')
            ->limit(8)
            ->get(['id', 'space_id', 'purpose', 'status', 'start_at', 'end_at']);

        $documents = Document::query()
            ->where('organization_id', $user->organization_id)
            ->with(['type:id,name'])
            ->latest()
            ->limit(50)
            ->get()
            ->filter(fn (Document $document) => $user->can('view', $document))
            ->take(8)
            ->values()
            ->map(fn (Document $document) => [
                'id' => $document->id,
                'title' => $document->title,
                'type' => $document->type?->name,
                'visibility' => $document->visibility,
                'status' => $document->status,
                'created_at' => $document->created_at,
            ]);

        $publicPlans = OperationalPlan::query()
            ->where('organization_id', $user->organization_id)
            ->whereIn('visibility', ['public', 'portal'])
            ->whereIn('status', ['approved', 'scheduled', 'in_progress', 'completed'])
            ->whereDate('start_date', '>=', now()->toDateString())
            ->orderBy('start_date')
            ->limit(8)
            ->get(['id', 'title', 'plan_type', 'status', 'start_date', 'end_date']);

        $activeStatuses = ['novo', 'em_analise', 'aguarda_informacao', 'encaminhado', 'em_execucao', 'agendado'];
        $resolvedStatuses = ['resolvido', 'fechado', 'cancelado', 'indeferido'];

        $unreadAlerts = NotificationRecipient::query()
            ->where('user_id', $user->id)
            ->whereNull('archived_at')
            ->whereNull('read_at')
            ->whereHas('notification', fn ($query) => $query->where('organization_id', $user->organization_id))
            ->count();

        return [
            'kpis' => [
                'my_active_tickets' => (clone $ticketsBase)->whereIn('status', $activeStatuses)->count(),
                'resolved_tickets' => (clone $ticketsBase)->whereIn('status', $resolvedStatuses)->count(),
                'unread_alerts' => $unreadAlerts,
                'upcoming_items' => $events->count() + $reservations->count(),
                'waiting_tickets' => (clone $ticketsBase)->whereIn('status', ['novo', 'em_analise', 'aguarda_informacao'])->count(),
                'upcoming_events' => $events->count(),
                'upcoming_reservations' => $reservations->count(),
                'available_documents' => $documents->count(),
            ],
            'tickets' => [
                'active' => (clone $ticketsBase)->whereNotIn('status', ['resolvido', 'fechado', 'cancelado', 'indeferido'])->latest()->limit(8)->get(['id', 'reference', 'title', 'status', 'priority', 'updated_at']),
                'recent_updates' => (clone $ticketsBase)->latest('updated_at')->limit(8)->get(['id', 'reference', 'title', 'status', 'updated_at']),
            ],
            'events' => $events,
            'reservations' => $reservations,
            'documents' => $documents,
            'public_plans' => $publicPlans,
        ];
    }
}
