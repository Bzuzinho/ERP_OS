<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\PortalDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the portal dashboard.
     */
    public function __invoke(PortalDashboardService $dashboardService): Response
    {
        $data = $dashboardService->getData(request()->user());

        return Inertia::render('Portal/Dashboard/Index', [
            'data' => $data,
        ]);
    }
}