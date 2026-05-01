<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportFilterRequest;
use App\Services\Dashboard\AdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function __invoke(ReportFilterRequest $request, AdminDashboardService $dashboardService): Response
    {
        $data = $dashboardService->getData($request->user(), $request->validated());

        return Inertia::render('Admin/Dashboard/Index', [
            'data' => $data,
            'filters' => $request->validated(),
        ]);
    }
}