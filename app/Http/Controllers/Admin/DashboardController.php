<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
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

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => [
                ['label' => 'Tarefas pendentes', 'value' => Task::query()->whereIn('status', ['pending', 'in_progress', 'waiting'])->count()],
                ['label' => 'Tarefas urgentes', 'value' => Task::query()->whereIn('status', ['pending', 'in_progress', 'waiting'])->where('priority', 'urgent')->count()],
                ['label' => 'Eventos hoje', 'value' => Event::query()->whereDate('start_at', now()->toDateString())->count()],
                ['label' => 'Eventos semana', 'value' => Event::query()->whereBetween('start_at', [now()->startOfWeek(), now()->endOfWeek()])->count()],
            ],
            'pendingTasks' => $pendingTasks,
            'todayEvents' => $todayEvents,
        ]);
    }
}