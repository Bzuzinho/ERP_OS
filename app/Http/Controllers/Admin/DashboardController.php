<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportFilterRequest;
use App\Services\Dashboard\AdminDashboardService;
use App\Services\Dashboard\OperationalDashboardService;
use App\Services\Scopes\UserOperationalScopeService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function __invoke(
        ReportFilterRequest $request,
        AdminDashboardService $dashboardService,
        OperationalDashboardService $operationalService,
        UserOperationalScopeService $scopeService,
    ): Response {
        $filters = $request->validated();
        $user = $request->user();
        $allMyScopes = (bool) ($filters['all_my_scopes'] ?? false);

        $context = $scopeService->validateContext(
            $user,
            $allMyScopes ? null : ($filters['organization_id'] ?? $user->defaultOrganization()?->id ?? $user->organization_id),
            $filters['department_id'] ?? null,
            $filters['service_area_id'] ?? null,
        );

        $data = $dashboardService->getDashboardDataForContext($context, $filters);

        $operational = [
            'resumo'               => $operationalService->getResumo($context),
            'agenda_hoje'          => $operationalService->getAgendaHoje($context),
            'proximas_atividades'  => $operationalService->getProximasAtividades($context),
            'alertas'              => $operationalService->getAlertas($context),
            'espacos'              => $operationalService->getEspacos($context),
            'recursos_humanos'     => $operationalService->getRecursosHumanos($context),
            'pedidos_recentes'     => $operationalService->getPedidosRecentes($context),
            'tarefas_em_curso'     => $operationalService->getTarefasEmCurso($context),
            'tarefas_por_validar'  => $operationalService->getTarefasPorValidar($context),
            'planos_operacionais'  => $operationalService->getPlanosOperacionais($context),
        ];

        $contextTree = $scopeService->buildContextTree($user);

        return Inertia::render('Admin/Dashboard/Index', [
            'data'        => $data,
            'filters'     => $filters,
            'operational' => $operational,
            'contextTree' => $contextTree,
        ]);
    }
}