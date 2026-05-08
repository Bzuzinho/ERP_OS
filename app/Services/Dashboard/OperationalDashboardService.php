<?php

namespace App\Services\Dashboard;

use App\Data\DashboardContext;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\OperationalPlan;
use App\Models\Organization;
use App\Models\Space;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OperationalDashboardService
{
    private const LIST_LIMIT = 10;

    // ──────────────────────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────────────────────

    public function getAgendaHoje(DashboardContext $context): array
    {
        $orgIds = $this->organizationIdsForContext($context);

        if ($orgIds === []) {
            return ['events' => [], 'reservations' => [], 'tasks' => []];
        }

        $today = now()->toDateString();

        $events = Event::query()
            ->whereIn('organization_id', $orgIds)
            ->whereDate('start_at', $today)
            ->orderBy('start_at')
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'title', 'event_type', 'status', 'start_at', 'end_at', 'location_text']);

        $reservations = SpaceReservation::query()
            ->whereIn('organization_id', $orgIds)
            ->whereDate('start_at', $today)
            ->whereIn('status', ['approved', 'completed', 'requested'])
            ->with(['space:id,name'])
            ->orderBy('start_at')
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'space_id', 'purpose', 'status', 'start_at', 'end_at']);

        $tasksQuery = Task::query()
            ->whereIn('organization_id', $orgIds)
            ->whereDate('due_date', $today)
            ->whereNotIn('status', ['done', 'cancelled', 'validated'])
            ->with(['assignee:id,name'])
            ->orderBy('priority');
        $this->applyContextToTask($tasksQuery, $context);

        $tasks = $tasksQuery
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'title', 'status', 'priority', 'due_date', 'assigned_to']);

        return [
            'events'       => $events,
            'reservations' => $reservations,
            'tasks'        => $tasks,
        ];
    }

    public function getProximasAtividades(DashboardContext $context): array
    {
        $orgIds = $this->organizationIdsForContext($context);

        if ($orgIds === []) {
            return [];
        }

        $today = now()->toDateString();

        $events = Event::query()
            ->whereIn('organization_id', $orgIds)
            ->whereDate('start_at', '>', $today)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('start_at')
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'title', 'event_type', 'status', 'start_at', 'end_at', 'location_text'])
            ->map(fn ($e) => array_merge($e->toArray(), ['_type' => 'event']));

        $reservations = SpaceReservation::query()
            ->whereIn('organization_id', $orgIds)
            ->whereDate('start_at', '>', $today)
            ->whereIn('status', ['approved', 'requested'])
            ->with(['space:id,name'])
            ->orderBy('start_at')
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'space_id', 'purpose', 'status', 'start_at', 'end_at'])
            ->map(fn ($r) => array_merge(
                $r->toArray(),
                ['_type' => 'reservation', 'title' => $r->purpose ?? ($r->space?->name ?? 'Reserva')]
            ));

        $plans = OperationalPlan::query()
            ->whereIn('organization_id', $orgIds)
            ->whereIn('status', ['approved', 'scheduled', 'in_progress'])
            ->whereDate('start_date', '>=', $today)
            ->orderBy('start_date')
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'title', 'plan_type', 'status', 'start_date', 'end_date'])
            ->map(fn ($p) => array_merge(
                $p->toArray(),
                ['_type' => 'plan', 'start_at' => $p->start_date]
            ));

        return collect($events)
            ->merge($reservations)
            ->merge($plans)
            ->sortBy('start_at')
            ->take(self::LIST_LIMIT)
            ->values()
            ->all();
    }

    public function getAlertas(DashboardContext $context): array
    {
        $orgIds = $this->organizationIdsForContext($context);

        if ($orgIds === []) {
            return $this->emptyAlertas();
        }

        $overdueTasksQuery = Task::query()
            ->whereIn('organization_id', $orgIds)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['done', 'cancelled', 'validated'])
            ->with(['assignee:id,name', 'ticket:id,reference,title']);
        $this->applyContextToTask($overdueTasksQuery, $context);
        $overdueTasks = $overdueTasksQuery
            ->orderBy('due_date')
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'title', 'status', 'priority', 'due_date', 'assigned_to', 'ticket_id']);

        $reopenedTasksQuery = Task::query()
            ->whereIn('organization_id', $orgIds)
            ->where('status', 'reopened')
            ->with(['assignee:id,name', 'ticket:id,reference,title']);
        $this->applyContextToTask($reopenedTasksQuery, $context);
        $reopenedTasks = $reopenedTasksQuery
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'title', 'status', 'priority', 'due_date', 'assigned_to', 'ticket_id']);

        $pendingValidationTasksQuery = Task::query()
            ->whereIn('organization_id', $orgIds)
            ->where('status', 'pending_validation')
            ->with(['assignee:id,name', 'ticket:id,reference,title']);
        $this->applyContextToTask($pendingValidationTasksQuery, $context);
        $pendingValidationTasks = $pendingValidationTasksQuery
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'title', 'status', 'priority', 'due_date', 'assigned_to', 'ticket_id']);

        $awaitingValidationTicketsQuery = Ticket::query()
            ->whereIn('organization_id', $orgIds)
            ->where('status', 'aguarda_validacao')
            ->with(['assignee:id,name']);
        $this->applyContextToTicket($awaitingValidationTicketsQuery, $context);
        $awaitingValidationTickets = $awaitingValidationTicketsQuery
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'reference', 'title', 'status', 'priority', 'assigned_to', 'due_date', 'department_id', 'service_area_id']);

        $pendingReservations = SpaceReservation::query()
            ->whereIn('organization_id', $orgIds)
            ->where('status', 'requested')
            ->with(['space:id,name'])
            ->orderBy('start_at')
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'space_id', 'purpose', 'status', 'start_at', 'end_at']);

        return [
            'overdue_tasks'                 => $overdueTasks,
            'reopened_tasks'                => $reopenedTasks,
            'pending_validation_tasks'      => $pendingValidationTasks,
            'awaiting_validation_tickets'   => $awaitingValidationTickets,
            'pending_reservations'          => $pendingReservations,
            'counts' => [
                'overdue_tasks'               => $overdueTasks->count(),
                'reopened_tasks'              => $reopenedTasks->count(),
                'pending_validation_tasks'    => $pendingValidationTasks->count(),
                'awaiting_validation_tickets' => $awaitingValidationTickets->count(),
                'pending_reservations'        => $pendingReservations->count(),
            ],
        ];
    }

    public function getEspacos(DashboardContext $context): array
    {
        $orgIds = $this->organizationIdsForContext($context);

        if ($orgIds === []) {
            return ['today' => [], 'pending' => [], 'reservation_tasks' => [], 'counts' => []];
        }

        $today = now()->toDateString();

        $todayReservations = SpaceReservation::query()
            ->whereIn('organization_id', $orgIds)
            ->whereDate('start_at', $today)
            ->with(['space:id,name,status'])
            ->orderBy('start_at')
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'space_id', 'purpose', 'status', 'start_at', 'end_at']);

        $pendingReservations = SpaceReservation::query()
            ->whereIn('organization_id', $orgIds)
            ->where('status', 'requested')
            ->with(['space:id,name'])
            ->orderBy('start_at')
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'space_id', 'purpose', 'status', 'start_at', 'end_at']);

        $reservationTasks = Task::query()
            ->whereIn('organization_id', $orgIds)
            ->whereNotNull('space_reservation_id')
            ->whereDate('due_date', $today)
            ->whereNotIn('status', ['done', 'cancelled', 'validated'])
            ->with(['assignee:id,name', 'spaceReservation:id,space_id,purpose,start_at,end_at', 'spaceReservation.space:id,name'])
            ->orderBy('due_date')
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'space_reservation_id', 'title', 'status', 'priority', 'due_date', 'assigned_to']);

        $occupiedSpacesToday = $todayReservations
            ->whereIn('status', ['approved', 'completed'])
            ->pluck('space_id')
            ->unique()
            ->count();

        $spacesInMaintenance = Space::query()
            ->whereIn('organization_id', $orgIds)
            ->where('status', 'maintenance')
            ->count();

        $reservationsWithoutTasks = SpaceReservation::query()
            ->whereIn('organization_id', $orgIds)
            ->whereDate('start_at', $today)
            ->where('status', 'approved')
            ->whereDoesntHave('tasks')
            ->count();

        return [
            'today'             => $todayReservations,
            'pending'           => $pendingReservations,
            'reservation_tasks' => $reservationTasks,
            'counts'            => [
                'today_total'                => $todayReservations->count(),
                'pending_approval'           => $pendingReservations->count(),
                'occupied_spaces_today'      => $occupiedSpacesToday,
                'spaces_in_maintenance'      => $spacesInMaintenance,
                'reservation_tasks_today'    => $reservationTasks->count(),
                'reservations_without_tasks' => $reservationsWithoutTasks,
            ],
        ];
    }

    public function getRecursosHumanos(DashboardContext $context): array
    {
        $orgIds = $this->organizationIdsForContext($context);

        if ($orgIds === []) {
            return ['absences' => [], 'task_summary' => [], 'counts' => []];
        }

        $today = now()->toDateString();

        $todayAbsences = AttendanceRecord::query()
            ->whereIn('organization_id', $orgIds)
            ->whereDate('date', $today)
            ->whereIn('status', ['absent', 'sick_leave', 'vacation', 'justified_absence', 'unjustified_absence'])
            ->with(['employee:id,employee_number,user_id,role_title'])
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'employee_id', 'status', 'date', 'organization_id']);

        $presentCount = AttendanceRecord::query()
            ->whereIn('organization_id', $orgIds)
            ->whereDate('date', $today)
            ->where('status', 'present')
            ->count();

        $taskSummaryQuery = Task::query()
            ->whereIn('organization_id', $orgIds)
            ->whereIn('status', ['in_progress', 'pending', 'waiting', 'reopened'])
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, COUNT(*) as task_count')
            ->groupBy('assigned_to')
            ->orderByDesc('task_count');
        $this->applyContextToTask($taskSummaryQuery, $context);
        $taskSummary = $taskSummaryQuery
            ->with(['assignee:id,name'])
            ->limit(8)
            ->get();

        return [
            'absences'     => $todayAbsences,
            'task_summary' => $taskSummary,
            'counts'       => [
                'present_today' => $presentCount,
                'absent_today'  => $todayAbsences->count(),
            ],
        ];
    }

    public function getPedidosRecentes(DashboardContext $context): array
    {
        $orgIds = $this->organizationIdsForContext($context);

        if ($orgIds === []) {
            return [];
        }

        $query = Ticket::query()
            ->whereIn('organization_id', $orgIds)
                ->whereNotIn('status', ['fechado', 'cancelado', 'indeferido'])
            ->with(['contact:id,name', 'assignee:id,name'])
            ->latest()
            ->limit(self::LIST_LIMIT);
        $this->applyContextToTicket($query, $context);

        return $query
            ->get(['id', 'reference', 'title', 'status', 'priority', 'category', 'assigned_to', 'contact_id', 'created_at', 'due_date', 'department_id', 'service_area_id'])
            ->all();
    }

    public function getTarefasEmCurso(DashboardContext $context): array
    {
        $orgIds = $this->organizationIdsForContext($context);

        if ($orgIds === []) {
            return [];
        }

        $query = Task::query()
            ->whereIn('organization_id', $orgIds)
            ->whereIn('status', ['in_progress', 'waiting', 'reopened'])
            ->with(['assignee:id,name', 'ticket:id,reference,title'])
            ->orderByRaw("CASE WHEN status = 'reopened' THEN 0 WHEN status = 'in_progress' THEN 1 ELSE 2 END")
            ->orderBy('due_date');
        $this->applyContextToTask($query, $context);

        $today = now()->toDateString();

        return $query
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'title', 'status', 'priority', 'due_date', 'assigned_to', 'ticket_id'])
            ->map(function ($task) use ($today) {
                $arr = $task->toArray();
                $arr['is_overdue'] = $task->due_date !== null
                    && $task->due_date < $today
                    && ! in_array($task->status, ['done', 'cancelled', 'validated'], true);

                return $arr;
            })
            ->all();
    }

    public function getTarefasPorValidar(DashboardContext $context): array
    {
        $orgIds = $this->organizationIdsForContext($context);

        if ($orgIds === []) {
            return [];
        }

        $query = Task::query()
            ->whereIn('organization_id', $orgIds)
            ->where('status', 'pending_validation')
            ->with(['assignee:id,name', 'ticket:id,reference,title'])
            ->orderBy('due_date');
        $this->applyContextToTask($query, $context);

        return $query
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'title', 'status', 'priority', 'due_date', 'assigned_to', 'ticket_id'])
            ->all();
    }

    public function getPlanosOperacionais(DashboardContext $context): array
    {
        $orgIds = $this->organizationIdsForContext($context);

        if ($orgIds === []) {
            return [];
        }

        return OperationalPlan::query()
            ->whereIn('organization_id', $orgIds)
            ->whereIn('status', ['approved', 'scheduled', 'in_progress'])
            ->with(['owner:id,name'])
            ->orderByDesc('updated_at')
            ->limit(self::LIST_LIMIT)
            ->get(['id', 'title', 'plan_type', 'status', 'progress_percent', 'start_date', 'end_date', 'owner_user_id'])
            ->all();
    }

    public function getResumo(DashboardContext $context): array
    {
        $orgIds = $this->organizationIdsForContext($context);

        if ($orgIds === []) {
            return $this->emptyResumo();
        }

        $ticketBase = Ticket::query()->whereIn('organization_id', $orgIds);
        $this->applyContextToTicket($ticketBase, $context);

        $taskBase = Task::query()->whereIn('organization_id', $orgIds);
        $this->applyContextToTask($taskBase, $context);

        $today = now()->toDateString();

        return [
            'open_tickets'                 => (clone $ticketBase)->whereNotIn('status', ['resolvido', 'fechado', 'cancelado', 'indeferido'])->count(),
            'urgent_tickets'               => (clone $ticketBase)->where('priority', 'urgent')->count(),
            'overdue_tickets'              => (clone $ticketBase)->whereNotNull('due_date')->whereDate('due_date', '<', $today)->whereNotIn('status', ['resolvido', 'fechado'])->count(),
            'awaiting_validation_tickets'  => (clone $ticketBase)->where('status', 'aguarda_validacao')->count(),
            'tasks_in_progress'            => (clone $taskBase)->whereIn('status', ['in_progress', 'waiting'])->count(),
            'tasks_pending_validation'     => (clone $taskBase)->where('status', 'pending_validation')->count(),
            'tasks_overdue'                => (clone $taskBase)->whereNotNull('due_date')->whereDate('due_date', '<', $today)->whereNotIn('status', ['done', 'cancelled', 'validated'])->count(),
            'tasks_reopened'               => (clone $taskBase)->where('status', 'reopened')->count(),
            'events_today'                 => Event::query()->whereIn('organization_id', $orgIds)->whereDate('start_at', $today)->count(),
            'reservations_today'           => SpaceReservation::query()->whereIn('organization_id', $orgIds)->whereDate('start_at', $today)->count(),
            'pending_reservations'         => SpaceReservation::query()->whereIn('organization_id', $orgIds)->where('status', 'requested')->count(),
            'active_plans'                 => OperationalPlan::query()->whereIn('organization_id', $orgIds)->whereIn('status', ['scheduled', 'in_progress'])->count(),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function organizationIdsForContext(DashboardContext $context): array
    {
        if (! $context->allMyScopes && $context->organizationId !== null) {
            return [$context->organizationId];
        }

        if ($context->user->hasRole('super_admin')) {
            return Organization::query()
                ->where('is_active', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $ids = $context->user->organizations()
            ->wherePivot('is_active', true)
            ->pluck('organizations.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($context->user->organization_id !== null) {
            $ids[] = (int) $context->user->organization_id;
        }

        return array_values(array_unique($ids));
    }

    private function applyContextToTicket(Builder $query, DashboardContext $context): void
    {
        if ($context->departmentId !== null) {
            $query->where('department_id', $context->departmentId);
        }

        if ($context->serviceAreaId !== null) {
            $query->where('service_area_id', $context->serviceAreaId);
        }
    }

    private function applyContextToTask(Builder $query, DashboardContext $context): void
    {
        if ($context->departmentId === null && $context->serviceAreaId === null) {
            return;
        }

        $query->whereHas('ticket', function (Builder $q) use ($context): void {
            $this->applyContextToTicket($q, $context);
        });
    }

    private function emptyAlertas(): array
    {
        return [
            'overdue_tasks'               => [],
            'reopened_tasks'              => [],
            'pending_validation_tasks'    => [],
            'awaiting_validation_tickets' => [],
            'pending_reservations'        => [],
            'counts' => [
                'overdue_tasks'               => 0,
                'reopened_tasks'              => 0,
                'pending_validation_tasks'    => 0,
                'awaiting_validation_tickets' => 0,
                'pending_reservations'        => 0,
            ],
        ];
    }

    private function emptyResumo(): array
    {
        return [
            'open_tickets'                => 0,
            'urgent_tickets'              => 0,
            'overdue_tickets'             => 0,
            'awaiting_validation_tickets' => 0,
            'tasks_in_progress'           => 0,
            'tasks_pending_validation'    => 0,
            'tasks_overdue'               => 0,
            'tasks_reopened'              => 0,
            'events_today'                => 0,
            'reservations_today'          => 0,
            'pending_reservations'        => 0,
            'active_plans'                => 0,
        ];
    }
}
