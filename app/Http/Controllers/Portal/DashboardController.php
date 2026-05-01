<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\SpaceReservation;
use App\Models\Ticket;
use App\Services\Planning\OperationalPlanningDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the portal dashboard.
     */
    public function __invoke(OperationalPlanningDashboardService $planningDashboard): Response
    {
        $user = request()->user();
        $contactIds = $user->contacts()->pluck('id');

        $upcomingEvents = Event::query()
            ->where('start_at', '>=', now())
            ->where(function ($query) use ($user, $contactIds) {
                $query
                    ->where('visibility', 'public')
                    ->orWhereHas('relatedContact', fn ($contactQuery) => $contactQuery->whereIn('id', $contactIds))
                    ->orWhereHas('participants', fn ($participantQuery) => $participantQuery
                        ->where('user_id', $user->id)
                        ->orWhereIn('contact_id', $contactIds));
            })
            ->orderBy('start_at')
            ->limit(5)
            ->get();

        $upcomingReservations = SpaceReservation::query()
            ->with(['space:id,name'])
            ->where('start_at', '>=', now())
            ->where(function ($query) use ($user, $contactIds) {
                $query->where('requested_by_user_id', $user->id)
                    ->orWhereIn('contact_id', $contactIds);
            })
            ->orderBy('start_at')
            ->limit(5)
            ->get();

        $upcomingPublicActivities = $planningDashboard->upcomingPublicActivities();

        return Inertia::render('Portal/Dashboard/Index', [
            'stats' => [
                ['label' => 'Pedidos ativos', 'value' => Ticket::query()->whereIn('contact_id', $contactIds)->whereNotIn('status', ['resolved', 'closed'])->count()],
                ['label' => 'Respostas pendentes', 'value' => Ticket::query()->whereIn('contact_id', $contactIds)->whereIn('status', ['new', 'in_progress'])->count()],
                ['label' => 'Proximas marcacoes', 'value' => $upcomingEvents->count()],
                ['label' => 'Proximas reservas', 'value' => $upcomingReservations->count()],
            ],
            'actions' => [
                'Novo pedido',
                'Consultar pedidos',
                'Consultar agenda',
                'Pedir reserva',
            ],
            'upcomingEvents' => $upcomingEvents,
            'upcomingReservations' => $upcomingReservations,
            'upcomingPublicActivities' => $upcomingPublicActivities,
        ]);
    }
}