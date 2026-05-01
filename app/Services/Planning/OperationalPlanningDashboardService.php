<?php

namespace App\Services\Planning;

use App\Models\OperationalPlan;
use App\Models\RecurringOperation;
use App\Models\RecurringOperationRun;
use App\Models\User;

class OperationalPlanningDashboardService
{
    public function adminStats(User $user): array
    {
        $organizationId = $user->organization_id;

        return [
            'planos_ativos' => OperationalPlan::query()
                ->where('organization_id', $organizationId)
                ->whereIn('status', ['approved', 'scheduled', 'in_progress'])
                ->count(),
            'planos_pendentes_aprovacao' => OperationalPlan::query()
                ->where('organization_id', $organizationId)
                ->where('status', 'pending_approval')
                ->count(),
            'planos_em_execucao' => OperationalPlan::query()
                ->where('organization_id', $organizationId)
                ->where('status', 'in_progress')
                ->count(),
            'planos_concluidos_mes' => OperationalPlan::query()
                ->where('organization_id', $organizationId)
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year)
                ->count(),
            'recorrencias_ativas' => RecurringOperation::query()
                ->where('organization_id', $organizationId)
                ->where('status', 'active')
                ->count(),
            'operacoes_recorrentes_falhadas' => RecurringOperationRun::query()
                ->whereHas('recurringOperation', fn ($query) => $query->where('organization_id', $organizationId))
                ->where('status', 'failed')
                ->count(),
        ];
    }

    public function runningPlans(User $user)
    {
        return OperationalPlan::query()
            ->where('organization_id', $user->organization_id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->with(['owner:id,name', 'department:id,name', 'team:id,name'])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();
    }

    public function upcomingRecurring(User $user)
    {
        return RecurringOperation::query()
            ->where('organization_id', $user->organization_id)
            ->where('status', 'active')
            ->whereNotNull('next_run_at')
            ->orderBy('next_run_at')
            ->limit(5)
            ->get();
    }

    public function upcomingPublicActivities()
    {
        return OperationalPlan::query()
            ->whereIn('visibility', ['public', 'portal'])
            ->whereIn('status', ['approved', 'scheduled', 'in_progress', 'completed'])
            ->whereDate('start_date', '>=', now()->toDateString())
            ->orderBy('start_date')
            ->limit(5)
            ->get();
    }
}
