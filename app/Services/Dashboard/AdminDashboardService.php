<?php

namespace App\Services\Dashboard;

use App\Data\DashboardContext;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\MeetingMinute;
use App\Models\Organization;
use App\Models\OperationalPlan;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AdminDashboardService
{
    public function __construct(
        private readonly TicketKpiService $ticketKpis,
        private readonly TaskKpiService $taskKpis,
        private readonly EventKpiService $eventKpis,
        private readonly SpaceKpiService $spaceKpis,
        private readonly InventoryKpiService $inventoryKpis,
        private readonly HrKpiService $hrKpis,
        private readonly PlanningKpiService $planningKpis,
        private readonly DocumentKpiService $documentKpis,
    ) {
    }

    public function getData(User $user, array $filters = []): array
    {
        $ticket = $this->ticketKpis->getSummary($filters, $user);
        $task = $this->taskKpis->getSummary($filters, $user);
        $event = $this->eventKpis->getSummary($filters, $user);
        $space = $this->spaceKpis->getSummary($filters, $user);
        $inventory = $this->inventoryKpis->getSummary($filters, $user);
        $hr = $this->hrKpis->getSummary($filters, $user);
        $planning = $this->planningKpis->getSummary($filters, $user);
        $document = $this->documentKpis->getSummary($filters, $user);

        return [
            'kpis' => [
                'open_tickets' => $ticket['open'],
                'urgent_tickets' => $ticket['urgent'],
                'overdue_tickets' => $ticket['overdue'],
                'closed_tickets_this_month' => Ticket::query()->where('organization_id', $user->organization_id)->whereIn('status', ['resolvido', 'fechado'])->whereMonth('closed_at', now()->month)->whereYear('closed_at', now()->year)->count(),
                'pending_tasks' => $task['pending'],
                'in_progress_tasks' => $task['in_progress'],
                'done_tasks_this_month' => Task::query()->where('organization_id', $user->organization_id)->where('status', 'done')->whereMonth('completed_at', now()->month)->whereYear('completed_at', now()->year)->count(),
                'events_today' => $event['today'],
                'reservations_today' => SpaceReservation::query()->where('organization_id', $user->organization_id)->whereDate('start_at', now()->toDateString())->count(),
                'pending_reservations' => $space['reservations_pending'],
                'low_stock_items' => $inventory['low_stock'],
                'overdue_loans' => $inventory['overdue_loans'],
                'present_employees_today' => $hr['present_today'],
                'absences_today' => $hr['absent_today'] + $hr['vacation_today'],
                'plans_in_execution' => $planning['in_progress'],
                'plans_pending_approval' => $planning['pending_approval'],
                'active_documents' => $document['active_documents'],
                'meeting_minutes_pending_or_approved' => [
                    'draft' => $document['meeting_minutes_draft'],
                    'approved' => $document['meeting_minutes_approved'],
                ],
            ],
            'ticket_status_breakdown' => $ticket['by_status'],
            'ticket_category_breakdown' => $ticket['by_category'],
            'recent_tickets' => Ticket::query()
                ->where('organization_id', $user->organization_id)
                ->with(['contact:id,name', 'assignee:id,name'])
                ->latest()
                ->limit(8)
                ->get(['id', 'reference', 'title', 'status', 'priority', 'category', 'assigned_to', 'contact_id', 'created_at', 'due_date']),
            'pending_tasks' => Task::query()
                ->where('organization_id', $user->organization_id)
                ->whereIn('status', ['pending', 'in_progress', 'waiting'])
                ->with(['assignee:id,name'])
                ->orderBy('due_date')
                ->limit(8)
                ->get(['id', 'title', 'status', 'priority', 'due_date', 'assigned_to']),
            'today_events' => Event::query()
                ->where('organization_id', $user->organization_id)
                ->whereDate('start_at', now()->toDateString())
                ->orderBy('start_at')
                ->limit(8)
                ->get(['id', 'title', 'event_type', 'status', 'start_at', 'end_at', 'location_text']),
            'today_reservations' => SpaceReservation::query()
                ->where('organization_id', $user->organization_id)
                ->with(['space:id,name', 'contact:id,name'])
                ->whereDate('start_at', now()->toDateString())
                ->orderBy('start_at')
                ->limit(8)
                ->get(['id', 'space_id', 'contact_id', 'purpose', 'status', 'start_at', 'end_at']),
            'low_stock_items' => InventoryItem::query()
                ->where('organization_id', $user->organization_id)
                ->whereNotNull('minimum_stock')
                ->whereColumn('current_stock', '<', 'minimum_stock')
                ->orderBy('current_stock')
                ->limit(8)
                ->get(['id', 'name', 'sku', 'current_stock', 'minimum_stock']),
            'today_absences' => AttendanceRecord::query()
                ->where('organization_id', $user->organization_id)
                ->whereDate('date', now()->toDateString())
                ->whereIn('status', ['absent', 'sick_leave', 'vacation', 'justified_absence', 'unjustified_absence'])
                ->with(['employee:id,employee_number'])
                ->limit(8)
                ->get(['id', 'employee_id', 'status', 'date']),
            'active_plans' => OperationalPlan::query()
                ->where('organization_id', $user->organization_id)
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->with(['owner:id,name'])
                ->orderByDesc('updated_at')
                ->limit(8)
                ->get(['id', 'title', 'plan_type', 'status', 'progress_percent', 'owner_user_id']),
            'upcoming_public_activities' => OperationalPlan::query()
                ->where('organization_id', $user->organization_id)
                ->whereIn('visibility', ['public', 'portal'])
                ->whereIn('status', ['approved', 'scheduled', 'in_progress'])
                ->whereDate('start_date', '>=', now()->toDateString())
                ->orderBy('start_date')
                ->limit(8)
                ->get(['id', 'title', 'plan_type', 'status', 'start_date', 'end_date']),
            'meeting_minutes' => [
                'draft' => MeetingMinute::query()->where('organization_id', $user->organization_id)->where('status', 'draft')->count(),
                'approved' => MeetingMinute::query()->where('organization_id', $user->organization_id)->where('status', 'approved')->count(),
            ],
        ];
    }

    public function getDashboardDataForContext(DashboardContext $context, array $filters = []): array
    {
        $organizationIds = $this->organizationIdsForContext($context);

        if ($organizationIds === []) {
            return $this->emptyDashboardData();
        }

        $ticketBase = Ticket::query()->whereIn('organization_id', $organizationIds);
        $this->applyTicketContextFilters($ticketBase, $context);

        $taskBase = Task::query()->whereIn('organization_id', $organizationIds);
        $this->applyTaskContextFilters($taskBase, $context);

        $closedStatuses = ['resolvido', 'fechado'];

        return [
            'kpis' => [
                'open_tickets' => (clone $ticketBase)->whereNotIn('status', [...$closedStatuses, 'cancelado', 'indeferido'])->count(),
                'urgent_tickets' => (clone $ticketBase)->where('priority', 'urgent')->count(),
                'overdue_tickets' => (clone $ticketBase)
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', now()->toDateString())
                    ->whereNotIn('status', $closedStatuses)
                    ->count(),
                'closed_tickets_this_month' => (clone $ticketBase)
                    ->whereIn('status', $closedStatuses)
                    ->whereMonth('closed_at', now()->month)
                    ->whereYear('closed_at', now()->year)
                    ->count(),
                'pending_tasks' => (clone $taskBase)->where('status', 'pending')->count(),
                'in_progress_tasks' => (clone $taskBase)->where('status', 'in_progress')->count(),
                'done_tasks_this_month' => (clone $taskBase)
                    ->where('status', 'done')
                    ->whereMonth('completed_at', now()->month)
                    ->whereYear('completed_at', now()->year)
                    ->count(),
                'events_today' => Event::query()
                    ->whereIn('organization_id', $organizationIds)
                    ->whereDate('start_at', now()->toDateString())
                    ->count(),
                'reservations_today' => SpaceReservation::query()
                    ->whereIn('organization_id', $organizationIds)
                    ->whereDate('start_at', now()->toDateString())
                    ->count(),
                'pending_reservations' => SpaceReservation::query()
                    ->whereIn('organization_id', $organizationIds)
                    ->where('status', 'requested')
                    ->count(),
                'low_stock_items' => InventoryItem::query()
                    ->whereIn('organization_id', $organizationIds)
                    ->whereNotNull('minimum_stock')
                    ->whereColumn('current_stock', '<', 'minimum_stock')
                    ->count(),
                'overdue_loans' => InventoryLoan::query()
                    ->whereIn('organization_id', $organizationIds)
                    ->whereIn('status', ['active', 'overdue'])
                    ->whereNotNull('expected_return_at')
                    ->whereDate('expected_return_at', '<', now()->toDateString())
                    ->count(),
                'present_employees_today' => AttendanceRecord::query()
                    ->whereIn('organization_id', $organizationIds)
                    ->whereDate('date', now()->toDateString())
                    ->where('status', 'present')
                    ->count(),
                'absences_today' => AttendanceRecord::query()
                    ->whereIn('organization_id', $organizationIds)
                    ->whereDate('date', now()->toDateString())
                    ->whereIn('status', ['absent', 'vacation'])
                    ->count(),
                'plans_in_execution' => OperationalPlan::query()
                    ->whereIn('organization_id', $organizationIds)
                    ->where('status', 'in_progress')
                    ->count(),
                'plans_pending_approval' => OperationalPlan::query()
                    ->whereIn('organization_id', $organizationIds)
                    ->where('status', 'pending_approval')
                    ->count(),
                'active_documents' => $this->documentKpis->getSummary([], $context->user)['active_documents'] ?? 0,
                'meeting_minutes_pending_or_approved' => [
                    'draft' => MeetingMinute::query()->whereIn('organization_id', $organizationIds)->where('status', 'draft')->count(),
                    'approved' => MeetingMinute::query()->whereIn('organization_id', $organizationIds)->where('status', 'approved')->count(),
                ],
            ],
            'ticket_status_breakdown' => (clone $ticketBase)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray(),
            'ticket_category_breakdown' => (clone $ticketBase)
                ->selectRaw("COALESCE(category, 'sem_categoria') as label, COUNT(*) as total")
                ->groupBy('label')
                ->pluck('total', 'label')
                ->toArray(),
            'recent_tickets' => (clone $ticketBase)
                ->with(['contact:id,name', 'assignee:id,name'])
                ->latest()
                ->limit(8)
                ->get(['id', 'reference', 'title', 'status', 'priority', 'category', 'assigned_to', 'contact_id', 'created_at', 'due_date']),
            'pending_tasks' => (clone $taskBase)
                ->whereIn('status', ['pending', 'in_progress', 'waiting'])
                ->with(['assignee:id,name'])
                ->orderBy('due_date')
                ->limit(8)
                ->get(['id', 'title', 'status', 'priority', 'due_date', 'assigned_to']),
            'today_events' => Event::query()
                ->whereIn('organization_id', $organizationIds)
                ->whereDate('start_at', now()->toDateString())
                ->orderBy('start_at')
                ->limit(8)
                ->get(['id', 'title', 'event_type', 'status', 'start_at', 'end_at', 'location_text']),
            'today_reservations' => SpaceReservation::query()
                ->whereIn('organization_id', $organizationIds)
                ->with(['space:id,name', 'contact:id,name'])
                ->whereDate('start_at', now()->toDateString())
                ->orderBy('start_at')
                ->limit(8)
                ->get(['id', 'space_id', 'contact_id', 'purpose', 'status', 'start_at', 'end_at']),
            'low_stock_items' => InventoryItem::query()
                ->whereIn('organization_id', $organizationIds)
                ->whereNotNull('minimum_stock')
                ->whereColumn('current_stock', '<', 'minimum_stock')
                ->orderBy('current_stock')
                ->limit(8)
                ->get(['id', 'name', 'sku', 'current_stock', 'minimum_stock']),
            'today_absences' => AttendanceRecord::query()
                ->whereIn('organization_id', $organizationIds)
                ->whereDate('date', now()->toDateString())
                ->whereIn('status', ['absent', 'sick_leave', 'vacation', 'justified_absence', 'unjustified_absence'])
                ->with(['employee:id,employee_number'])
                ->limit(8)
                ->get(['id', 'employee_id', 'status', 'date']),
            'active_plans' => OperationalPlan::query()
                ->whereIn('organization_id', $organizationIds)
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->with(['owner:id,name'])
                ->orderByDesc('updated_at')
                ->limit(8)
                ->get(['id', 'title', 'plan_type', 'status', 'progress_percent', 'owner_user_id']),
            'upcoming_public_activities' => OperationalPlan::query()
                ->whereIn('organization_id', $organizationIds)
                ->whereIn('visibility', ['public', 'portal'])
                ->whereIn('status', ['approved', 'scheduled', 'in_progress'])
                ->whereDate('start_date', '>=', now()->toDateString())
                ->orderBy('start_date')
                ->limit(8)
                ->get(['id', 'title', 'plan_type', 'status', 'start_date', 'end_date']),
            'meeting_minutes' => [
                'draft' => MeetingMinute::query()->whereIn('organization_id', $organizationIds)->where('status', 'draft')->count(),
                'approved' => MeetingMinute::query()->whereIn('organization_id', $organizationIds)->where('status', 'approved')->count(),
            ],
        ];
    }

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

        $organizationIds = $context->user->organizations()
            ->wherePivot('is_active', true)
            ->pluck('organizations.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($context->user->organization_id !== null) {
            $organizationIds[] = (int) $context->user->organization_id;
        }

        return array_values(array_unique($organizationIds));
    }

    private function applyTicketContextFilters(Builder $query, DashboardContext $context): Builder
    {
        if ($context->departmentId !== null) {
            $query->where('department_id', $context->departmentId);
        }

        if ($context->serviceAreaId !== null) {
            $query->where('service_area_id', $context->serviceAreaId);
        }

        return $query;
    }

    private function applyTaskContextFilters(Builder $query, DashboardContext $context): Builder
    {
        if ($context->departmentId === null && $context->serviceAreaId === null) {
            return $query;
        }

        return $query->whereHas('ticket', function (Builder $ticketQuery) use ($context): void {
            $this->applyTicketContextFilters($ticketQuery, $context);
        });
    }

    private function emptyDashboardData(): array
    {
        return [
            'kpis' => [
                'open_tickets' => 0,
                'urgent_tickets' => 0,
                'overdue_tickets' => 0,
                'closed_tickets_this_month' => 0,
                'pending_tasks' => 0,
                'in_progress_tasks' => 0,
                'done_tasks_this_month' => 0,
                'events_today' => 0,
                'reservations_today' => 0,
                'pending_reservations' => 0,
                'low_stock_items' => 0,
                'overdue_loans' => 0,
                'present_employees_today' => 0,
                'absences_today' => 0,
                'plans_in_execution' => 0,
                'plans_pending_approval' => 0,
                'active_documents' => 0,
                'meeting_minutes_pending_or_approved' => [
                    'draft' => 0,
                    'approved' => 0,
                ],
            ],
            'ticket_status_breakdown' => [],
            'ticket_category_breakdown' => [],
            'recent_tickets' => collect(),
            'pending_tasks' => collect(),
            'today_events' => collect(),
            'today_reservations' => collect(),
            'low_stock_items' => collect(),
            'today_absences' => collect(),
            'active_plans' => collect(),
            'upcoming_public_activities' => collect(),
            'meeting_minutes' => [
                'draft' => 0,
                'approved' => 0,
            ],
        ];
    }
}
