<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ExportReportRequest;
use App\Services\Reports\CsvExportService;
use App\Services\Reports\DocumentReportService;
use App\Services\Reports\EventReportService;
use App\Services\Reports\HrReportService;
use App\Services\Reports\InventoryReportService;
use App\Services\Reports\PlanningReportService;
use App\Services\Reports\SpaceReportService;
use App\Services\Reports\TaskReportService;
use App\Services\Reports\TicketReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __invoke(
        ExportReportRequest $request,
        CsvExportService $csvExport,
        TicketReportService $ticketReports,
        TaskReportService $taskReports,
        EventReportService $eventReports,
        SpaceReportService $spaceReports,
        InventoryReportService $inventoryReports,
        HrReportService $hrReports,
        PlanningReportService $planningReports,
        DocumentReportService $documentReports,
    ): StreamedResponse {
        $user = $request->user();
        $filters = $request->validated();
        $reportType = $filters['report_type'];

        if (! $user->can('reports.export')) {
            abort(403);
        }

        $permissionMap = [
            'tickets' => 'reports.tickets',
            'tasks' => 'reports.tasks',
            'events' => 'reports.events',
            'spaces' => 'reports.spaces',
            'inventory' => 'reports.inventory',
            'hr' => 'reports.hr',
            'planning' => 'reports.planning',
            'documents' => 'reports.documents',
        ];

        if (! isset($permissionMap[$reportType]) || ! $user->can($permissionMap[$reportType])) {
            abort(403);
        }

        $serviceMap = [
            'tickets' => $ticketReports,
            'tasks' => $taskReports,
            'events' => $eventReports,
            'spaces' => $spaceReports,
            'inventory' => $inventoryReports,
            'hr' => $hrReports,
            'planning' => $planningReports,
            'documents' => $documentReports,
        ];

        $rows = $serviceMap[$reportType]->exportRows($filters, $user);

        return $csvExport->streamDownload($reportType, $rows);
    }
}
