<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportFilterRequest;
use App\Services\Dashboard\AdminDashboardService;
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

        return Inertia::render('Admin/Dashboard/Index', [
            'data' => $data,
            'filters' => $filters,
        ]);
    }
}