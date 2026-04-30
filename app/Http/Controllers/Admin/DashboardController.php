<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Space;
use App\Models\SpaceCleaningRecord;
use App\Models\SpaceMaintenanceRecord;
use App\Models\SpaceReservation;
use App\Models\Task;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function __invoke(): Response
    {
        $pendingTasks = Task::query()
            ->with(['assignee:id,name'])
            ->whereIn('status', ['pending', 'in_progress', 'waiting'])
            ->latest()
            ->limit(8)
            ->get();

        $todayEvents = Event::query()
            ->whereDate('start_at', now()->toDateString())
            ->orderBy('start_at')
            ->limit(8)
            ->get();

        $todayReservations = SpaceReservation::query()
            ->with(['space:id,name', 'contact:id,name'])
            ->whereDate('start_at', now()->toDateString())
            ->orderBy('start_at')
            ->limit(8)
            ->get();

        $pendingMaintenance = SpaceMaintenanceRecord::query()
            ->with(['space:id,name', 'assignee:id,name'])
            ->whereIn('status', ['pending', 'scheduled', 'in_progress'])
            ->latest()
            ->limit(8)
            ->get();

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => [
                ['label' => 'Espacos ativos', 'value' => Space::query()->where('is_active', true)->count()],
                ['label' => 'Reservas hoje', 'value' => SpaceReservation::query()->whereDate('start_at', now()->toDateString())->count()],
                ['label' => 'Reservas pendentes', 'value' => SpaceReservation::query()->where('status', 'requested')->count()],
                ['label' => 'Manutencoes abertas', 'value' => SpaceMaintenanceRecord::query()->whereIn('status', ['pending', 'scheduled', 'in_progress'])->count()],
                ['label' => 'Limpezas pendentes', 'value' => SpaceCleaningRecord::query()->whereIn('status', ['pending', 'scheduled', 'in_progress'])->count()],
            ],
            'pendingTasks' => $pendingTasks,
            'todayEvents' => $todayEvents,
            'todayReservations' => $todayReservations,
            'pendingMaintenance' => $pendingMaintenance,
        ]);
    }
}