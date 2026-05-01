<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportFilterRequest;
use App\Services\Reports\DocumentReportService;
use App\Services\Reports\EventReportService;
use App\Services\Reports\HrReportService;
use App\Services\Reports\InventoryReportService;
use App\Services\Reports\PlanningReportService;
use App\Services\Reports\SpaceReportService;
use App\Services\Reports\TaskReportService;
use App\Services\Reports\TicketReportService;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(ReportFilterRequest $request): Response
    {
        $this->authorizeReport($request, 'reports.view');

        $filters = $request->validated();
        $user = $request->user();

        return Inertia::render('Admin/Reports/Index', [
            'filters' => $filters,
            'cards' => [
                ['key' => 'tickets', 'title' => 'Pedidos', 'description' => 'Indicadores e listagem de pedidos', 'route' => route('admin.reports.tickets')],
                ['key' => 'tasks', 'title' => 'Tarefas', 'description' => 'Carga e execução operacional', 'route' => route('admin.reports.tasks')],
                ['key' => 'events', 'title' => 'Agenda/Eventos', 'description' => 'Agenda e eventos associados', 'route' => route('admin.reports.events')],
                ['key' => 'spaces', 'title' => 'Espaços/Reservas', 'description' => 'Reservas, ocupação e estado', 'route' => route('admin.reports.spaces')],
                ['key' => 'inventory', 'title' => 'Inventário', 'description' => 'Stock, movimentos e empréstimos', 'route' => route('admin.reports.inventory')],
                ['key' => 'hr', 'title' => 'Recursos Humanos', 'description' => 'Presenças, ausências e equipas', 'route' => route('admin.reports.hr')],
                ['key' => 'planning', 'title' => 'Planeamento', 'description' => 'Planos, progresso e recorrências', 'route' => route('admin.reports.planning')],
                ['key' => 'documents', 'title' => 'Documentos', 'description' => 'Documentação e atas', 'route' => route('admin.reports.documents')],
            ],
            'quick_kpis' => [
                'organization_id' => $user->organization_id,
            ],
        ]);
    }

    public function tickets(ReportFilterRequest $request, TicketReportService $service): Response
    {
        return $this->renderReport($request, 'reports.tickets', 'Admin/Reports/Tickets', $service, 'tickets');
    }

    public function tasks(ReportFilterRequest $request, TaskReportService $service): Response
    {
        return $this->renderReport($request, 'reports.tasks', 'Admin/Reports/Tasks', $service, 'tasks');
    }

    public function events(ReportFilterRequest $request, EventReportService $service): Response
    {
        return $this->renderReport($request, 'reports.events', 'Admin/Reports/Events', $service, 'events');
    }

    public function spaces(ReportFilterRequest $request, SpaceReportService $service): Response
    {
        return $this->renderReport($request, 'reports.spaces', 'Admin/Reports/Spaces', $service, 'spaces');
    }

    public function inventory(ReportFilterRequest $request, InventoryReportService $service): Response
    {
        return $this->renderReport($request, 'reports.inventory', 'Admin/Reports/Inventory', $service, 'inventory');
    }

    public function hr(ReportFilterRequest $request, HrReportService $service): Response
    {
        return $this->renderReport($request, 'reports.hr', 'Admin/Reports/Hr', $service, 'hr');
    }

    public function planning(ReportFilterRequest $request, PlanningReportService $service): Response
    {
        return $this->renderReport($request, 'reports.planning', 'Admin/Reports/Planning', $service, 'planning');
    }

    public function documents(ReportFilterRequest $request, DocumentReportService $service): Response
    {
        return $this->renderReport($request, 'reports.documents', 'Admin/Reports/Documents', $service, 'documents');
    }

    private function renderReport(ReportFilterRequest $request, string $permission, string $page, object $service, string $reportType): Response
    {
        $this->authorizeReport($request, $permission);

        $filters = $request->validated();
        $user = $request->user();

        return Inertia::render($page, [
            'reportType' => $reportType,
            'filters' => $filters,
            'summary' => $service->getSummary($filters, $user),
            'rows' => $service->getRows($filters, $user),
            'can' => [
                'export' => $user->can('reports.export'),
            ],
        ]);
    }

    private function authorizeReport(ReportFilterRequest $request, string $permission): void
    {
        $user = $request->user();

        if (! $user->can('reports.view') || ! $user->can($permission)) {
            abort(403);
        }
    }
}
