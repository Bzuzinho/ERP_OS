<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Event;
use App\Models\InventoryBreakage;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\InventoryRestockRequest;
use App\Models\LeaveRequest;
use App\Models\Space;
use App\Models\SpaceCleaningRecord;
use App\Models\SpaceMaintenanceRecord;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\Team;
use App\Services\Planning\OperationalPlanningDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function __invoke(OperationalPlanningDashboardService $planningDashboard): Response
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

        $lowStockItems = InventoryItem::query()
            ->with(['category:id,name'])
            ->whereNotNull('minimum_stock')
            ->whereColumn('current_stock', '<', 'minimum_stock')
            ->orderBy('current_stock')
            ->limit(8)
            ->get();

        $overdueLoans = InventoryLoan::query()
            ->with(['item:id,name,sku', 'borrowerUser:id,name', 'borrowerContact:id,name'])
            ->where(function ($query) {
                $query->where('status', 'overdue')
                    ->orWhere(function ($activeQuery) {
                        $activeQuery->where('status', 'active')
                            ->whereNotNull('expected_return_at')
                            ->where('expected_return_at', '<', now());
                    });
            })
            ->orderBy('expected_return_at')
            ->limit(8)
            ->get();

        $planningStats = $planningDashboard->adminStats(auth()->user());
        $runningPlans = $planningDashboard->runningPlans(auth()->user());
        $upcomingRecurring = $planningDashboard->upcomingRecurring(auth()->user());

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => [
                ['label' => 'Espacos ativos', 'value' => Space::query()->where('is_active', true)->count()],
                ['label' => 'Reservas hoje', 'value' => SpaceReservation::query()->whereDate('start_at', now()->toDateString())->count()],
                ['label' => 'Reservas pendentes', 'value' => SpaceReservation::query()->where('status', 'requested')->count()],
                ['label' => 'Manutencoes abertas', 'value' => SpaceMaintenanceRecord::query()->whereIn('status', ['pending', 'scheduled', 'in_progress'])->count()],
                ['label' => 'Limpezas pendentes', 'value' => SpaceCleaningRecord::query()->whereIn('status', ['pending', 'scheduled', 'in_progress'])->count()],
                ['label' => 'Itens ativos', 'value' => InventoryItem::query()->where('is_active', true)->count()],
                ['label' => 'Stock baixo', 'value' => InventoryItem::query()->whereNotNull('minimum_stock')->whereColumn('current_stock', '<', 'minimum_stock')->count()],
                ['label' => 'Emprestimos ativos', 'value' => InventoryLoan::query()->whereIn('status', ['active', 'overdue'])->count()],
                ['label' => 'Emprestimos em atraso', 'value' => InventoryLoan::query()->where(function ($query) {
                    $query->where('status', 'overdue')
                        ->orWhere(function ($activeQuery) {
                            $activeQuery->where('status', 'active')->whereNotNull('expected_return_at')->where('expected_return_at', '<', now());
                        });
                })->count()],
                ['label' => 'Reposicoes pendentes', 'value' => InventoryRestockRequest::query()->where('status', 'requested')->count()],
                ['label' => 'Quebras reportadas', 'value' => InventoryBreakage::query()->where('status', 'reported')->count()],
                ['label' => 'Planos ativos', 'value' => $planningStats['planos_ativos']],
                ['label' => 'Planos pendentes aprovação', 'value' => $planningStats['planos_pendentes_aprovacao']],
                ['label' => 'Planos em execução', 'value' => $planningStats['planos_em_execucao']],
                ['label' => 'Planos concluídos mês', 'value' => $planningStats['planos_concluidos_mes']],
                ['label' => 'Recorrências ativas', 'value' => $planningStats['recorrencias_ativas']],
                ['label' => 'Operações recorrentes falhadas', 'value' => $planningStats['operacoes_recorrentes_falhadas']],
            ],
            'hrStats' => [
                ['label' => 'Funcionários ativos', 'value' => Employee::query()->where('organization_id', auth()->user()->organization_id)->where('is_active', true)->count()],
                ['label' => 'Presentes hoje', 'value' => AttendanceRecord::query()->where('organization_id', auth()->user()->organization_id)->whereDate('date', now()->toDateString())->where('status', 'present')->count()],
                ['label' => 'Ausentes hoje', 'value' => AttendanceRecord::query()->where('organization_id', auth()->user()->organization_id)->whereDate('date', now()->toDateString())->whereIn('status', ['absent', 'sick_leave', 'vacation'])->count()],
                ['label' => 'Pedidos ausência pendentes', 'value' => LeaveRequest::query()->where('organization_id', auth()->user()->organization_id)->where('status', 'requested')->count()],
                ['label' => 'Equipas ativas', 'value' => Team::query()->where('organization_id', auth()->user()->organization_id)->where('is_active', true)->count()],
            ],
            'pendingLeaveRequests' => LeaveRequest::query()
                ->where('organization_id', auth()->user()->organization_id)
                ->where('status', 'requested')
                ->with('employee:id,employee_number')
                ->orderBy('start_date')
                ->limit(5)
                ->get(),
            'pendingTasks' => $pendingTasks,
            'todayEvents' => $todayEvents,
            'todayReservations' => $todayReservations,
            'pendingMaintenance' => $pendingMaintenance,
            'lowStockItems' => $lowStockItems,
            'overdueLoans' => $overdueLoans,
            'runningPlans' => $runningPlans,
            'upcomingRecurring' => $upcomingRecurring,
        ]);
    }
}