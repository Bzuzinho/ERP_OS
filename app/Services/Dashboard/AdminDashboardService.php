<?php

namespace App\Services\Dashboard;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\InventoryItem;
use App\Models\LeaveRequest;
use App\Models\MeetingMinute;
use App\Models\OperationalPlan;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;

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
}
