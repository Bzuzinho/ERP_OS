<?php

use App\Http\Controllers\Admin\AttachmentDownloadController as AdminAttachmentDownloadController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocumentAccessRuleController as AdminDocumentAccessRuleController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\DocumentDownloadController as AdminDocumentDownloadController;
use App\Http\Controllers\Admin\DocumentTypeController as AdminDocumentTypeController;
use App\Http\Controllers\Admin\DocumentVersionController as AdminDocumentVersionController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\EventParticipantController as AdminEventParticipantController;
use App\Http\Controllers\Admin\EventStatusController as AdminEventStatusController;
use App\Http\Controllers\Admin\InventoryBreakageController as AdminInventoryBreakageController;
use App\Http\Controllers\Admin\InventoryBreakageResolutionController as AdminInventoryBreakageResolutionController;
use App\Http\Controllers\Admin\InventoryCategoryController as AdminInventoryCategoryController;
use App\Http\Controllers\Admin\InventoryItemController as AdminInventoryItemController;
use App\Http\Controllers\Admin\InventoryItemStatusController as AdminInventoryItemStatusController;
use App\Http\Controllers\Admin\InventoryLoanController as AdminInventoryLoanController;
use App\Http\Controllers\Admin\InventoryLoanReturnController as AdminInventoryLoanReturnController;
use App\Http\Controllers\Admin\InventoryLocationController as AdminInventoryLocationController;
use App\Http\Controllers\Admin\InventoryMovementController as AdminInventoryMovementController;
use App\Http\Controllers\Admin\InventoryRestockApprovalController as AdminInventoryRestockApprovalController;
use App\Http\Controllers\Admin\InventoryRestockRequestController as AdminInventoryRestockRequestController;
use App\Http\Controllers\Admin\MeetingMinuteApprovalController as AdminMeetingMinuteApprovalController;
use App\Http\Controllers\Admin\MeetingMinuteController as AdminMeetingMinuteController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ReportExportController as AdminReportExportController;
use App\Http\Controllers\Admin\SpaceCleaningRecordController as AdminSpaceCleaningRecordController;
use App\Http\Controllers\Admin\SpaceCleaningStatusController as AdminSpaceCleaningStatusController;
use App\Http\Controllers\Admin\SpaceController as AdminSpaceController;
use App\Http\Controllers\Admin\SpaceMaintenanceRecordController as AdminSpaceMaintenanceRecordController;
use App\Http\Controllers\Admin\SpaceMaintenanceStatusController as AdminSpaceMaintenanceStatusController;
use App\Http\Controllers\Admin\SpaceReservationApprovalController as AdminSpaceReservationApprovalController;
use App\Http\Controllers\Admin\SpaceReservationCancellationController as AdminSpaceReservationCancellationController;
use App\Http\Controllers\Admin\SpaceReservationController as AdminSpaceReservationController;
use App\Http\Controllers\Admin\SpaceStatusController as AdminSpaceStatusController;
use App\Http\Controllers\Admin\TaskChecklistController as AdminTaskChecklistController;
use App\Http\Controllers\Admin\TaskChecklistItemController as AdminTaskChecklistItemController;
use App\Http\Controllers\Admin\TaskController as AdminTaskController;
use App\Http\Controllers\Admin\TaskStatusController as AdminTaskStatusController;
use App\Http\Controllers\Admin\TicketAttachmentController as AdminTicketAttachmentController;
use App\Http\Controllers\Admin\TicketCommentController as AdminTicketCommentController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\TicketStatusController as AdminTicketStatusController;
use App\Http\Controllers\Portal\AttachmentDownloadController as PortalAttachmentDownloadController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Portal\DocumentController as PortalDocumentController;
use App\Http\Controllers\Portal\DocumentDownloadController as PortalDocumentDownloadController;
use App\Http\Controllers\Portal\EventController as PortalEventController;
use App\Http\Controllers\Portal\MeetingMinuteController as PortalMeetingMinuteController;
use App\Http\Controllers\Portal\OperationalPlanController as PortalOperationalPlanController;
use App\Http\Controllers\Portal\SpaceController as PortalSpaceController;
use App\Http\Controllers\Portal\SpaceReservationCancellationController as PortalSpaceReservationCancellationController;
use App\Http\Controllers\Portal\SpaceReservationController as PortalSpaceReservationController;
use App\Http\Controllers\Portal\TicketAttachmentController as PortalTicketAttachmentController;
use App\Http\Controllers\Portal\TicketCommentController as PortalTicketCommentController;
use App\Http\Controllers\Portal\TicketController as PortalTicketController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (Request $request) {
    if (!$request->user()) {
        return to_route('login');
    }

    return $request->user()->can('admin.access')
        ? to_route('admin.dashboard')
        : to_route('portal.dashboard');
});

Route::get('/dashboard', function (Request $request) {
    return $request->user()->can('admin.access')
        ? to_route('admin.dashboard')
        : to_route('portal.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth', 'permission:admin.access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/more', fn () => Inertia::render('Admin/More/Index'))->name('more.index');

        Route::resource('contacts', AdminContactController::class);

        Route::resource('tickets', AdminTicketController::class);
        Route::patch('tickets/{ticket}/status', [AdminTicketStatusController::class, 'update'])->name('tickets.status.update');
        Route::patch('tickets/{ticket}/assign', [AdminTicketController::class, 'assign'])->name('tickets.assign');
        Route::post('tickets/{ticket}/comments', [AdminTicketCommentController::class, 'store'])->name('tickets.comments.store');
        Route::post('tickets/{ticket}/attachments', [AdminTicketAttachmentController::class, 'store'])->name('tickets.attachments.store');
        Route::get('attachments/{attachment}/download', AdminAttachmentDownloadController::class)->name('attachments.download');

        Route::resource('tasks', AdminTaskController::class);
        Route::patch('tasks/{task}/status', [AdminTaskStatusController::class, 'update'])->name('tasks.status.update');
        Route::post('tasks/{task}/complete', [AdminTaskController::class, 'complete'])->name('tasks.complete');
        Route::post('tasks/{task}/checklists', [AdminTaskChecklistController::class, 'store'])->name('tasks.checklists.store');
        Route::patch('tasks/{task}/checklists/{checklist}', [AdminTaskChecklistController::class, 'update'])->name('tasks.checklists.update');
        Route::delete('tasks/{task}/checklists/{checklist}', [AdminTaskChecklistController::class, 'destroy'])->name('tasks.checklists.destroy');
        Route::post('tasks/{task}/checklists/{checklist}/items', [AdminTaskChecklistItemController::class, 'store'])->name('tasks.checklists.items.store');
        Route::patch('tasks/{task}/checklists/{checklist}/items/{item}', [AdminTaskChecklistItemController::class, 'update'])->name('tasks.checklists.items.update');
        Route::delete('tasks/{task}/checklists/{checklist}/items/{item}', [AdminTaskChecklistItemController::class, 'destroy'])->name('tasks.checklists.items.destroy');

        Route::resource('events', AdminEventController::class);
        Route::patch('events/{event}/status', [AdminEventStatusController::class, 'update'])->name('events.status.update');
        Route::post('events/{event}/participants', [AdminEventParticipantController::class, 'store'])->name('events.participants.store');
        Route::delete('events/{event}/participants/{participant}', [AdminEventParticipantController::class, 'destroy'])->name('events.participants.destroy');

        Route::resource('document-types', AdminDocumentTypeController::class)
            ->parameters(['document-types' => 'documentType']);

        Route::resource('documents', AdminDocumentController::class);
        Route::get('documents/{document}/download', AdminDocumentDownloadController::class)->name('documents.download');
        Route::post('documents/{document}/versions', [AdminDocumentVersionController::class, 'store'])->name('documents.versions.store');
        Route::post('documents/{document}/access-rules', [AdminDocumentAccessRuleController::class, 'store'])->name('documents.access-rules.store');
        Route::delete('documents/{document}/access-rules/{documentAccessRule}', [AdminDocumentAccessRuleController::class, 'destroy'])->name('documents.access-rules.destroy');

        Route::resource('meeting-minutes', AdminMeetingMinuteController::class)
            ->parameters(['meeting-minutes' => 'meetingMinute']);
        Route::post('meeting-minutes/{meetingMinute}/approve', AdminMeetingMinuteApprovalController::class)->name('meeting-minutes.approve');

        Route::resource('spaces', AdminSpaceController::class);
        Route::patch('spaces/{space}/status', [AdminSpaceStatusController::class, 'update'])->name('spaces.status.update');

        Route::resource('space-reservations', AdminSpaceReservationController::class)
            ->parameters(['space-reservations' => 'spaceReservation']);
        Route::post('space-reservations/{spaceReservation}/approve', [AdminSpaceReservationApprovalController::class, 'approve'])->name('space-reservations.approve');
        Route::post('space-reservations/{spaceReservation}/reject', [AdminSpaceReservationApprovalController::class, 'reject'])->name('space-reservations.reject');
        Route::post('space-reservations/{spaceReservation}/complete', [AdminSpaceReservationApprovalController::class, 'complete'])->name('space-reservations.complete');
        Route::post('space-reservations/{spaceReservation}/cancel', AdminSpaceReservationCancellationController::class)->name('space-reservations.cancel');

        Route::resource('space-maintenance', AdminSpaceMaintenanceRecordController::class)
            ->parameters(['space-maintenance' => 'spaceMaintenance']);
        Route::patch('space-maintenance/{spaceMaintenance}/status', [AdminSpaceMaintenanceStatusController::class, 'update'])->name('space-maintenance.status.update');

        Route::resource('space-cleaning', AdminSpaceCleaningRecordController::class)
            ->parameters(['space-cleaning' => 'spaceCleaning']);
        Route::post('space-cleaning/{spaceCleaning}/complete', [AdminSpaceCleaningStatusController::class, 'complete'])->name('space-cleaning.complete');

        Route::get('inventory', fn () => to_route('admin.inventory-items.index'))->name('inventory.index');

        Route::resource('inventory-categories', AdminInventoryCategoryController::class)
            ->except(['show'])
            ->parameters(['inventory-categories' => 'inventoryCategory']);

        Route::resource('inventory-locations', AdminInventoryLocationController::class)
            ->except(['show'])
            ->parameters(['inventory-locations' => 'inventoryLocation']);

        Route::resource('inventory-items', AdminInventoryItemController::class)
            ->parameters(['inventory-items' => 'inventoryItem']);
        Route::patch('inventory-items/{inventoryItem}/status', [AdminInventoryItemStatusController::class, 'update'])->name('inventory-items.status.update');

        Route::resource('inventory-movements', AdminInventoryMovementController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['inventory-movements' => 'inventoryMovement']);

        Route::resource('inventory-loans', AdminInventoryLoanController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['inventory-loans' => 'inventoryLoan']);
        Route::post('inventory-loans/{inventoryLoan}/return', AdminInventoryLoanReturnController::class)->name('inventory-loans.return');

        Route::resource('inventory-restock-requests', AdminInventoryRestockRequestController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['inventory-restock-requests' => 'inventoryRestockRequest']);
        Route::post('inventory-restock-requests/{inventoryRestockRequest}/approve', [AdminInventoryRestockApprovalController::class, 'approve'])->name('inventory-restock-requests.approve');
        Route::post('inventory-restock-requests/{inventoryRestockRequest}/reject', [AdminInventoryRestockApprovalController::class, 'reject'])->name('inventory-restock-requests.reject');
        Route::post('inventory-restock-requests/{inventoryRestockRequest}/complete', [AdminInventoryRestockApprovalController::class, 'complete'])->name('inventory-restock-requests.complete');

        Route::resource('inventory-breakages', AdminInventoryBreakageController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['inventory-breakages' => 'inventoryBreakage']);
        Route::post('inventory-breakages/{inventoryBreakage}/resolve', AdminInventoryBreakageResolutionController::class)->name('inventory-breakages.resolve');

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [AdminReportController::class, 'index'])
                ->middleware('permission:reports.view')
                ->name('index');
            Route::get('/tickets', [AdminReportController::class, 'tickets'])
                ->middleware('permission:reports.tickets')
                ->name('tickets');
            Route::get('/tasks', [AdminReportController::class, 'tasks'])
                ->middleware('permission:reports.tasks')
                ->name('tasks');
            Route::get('/events', [AdminReportController::class, 'events'])
                ->middleware('permission:reports.events')
                ->name('events');
            Route::get('/spaces', [AdminReportController::class, 'spaces'])
                ->middleware('permission:reports.spaces')
                ->name('spaces');
            Route::get('/inventory', [AdminReportController::class, 'inventory'])
                ->middleware('permission:reports.inventory')
                ->name('inventory');
            Route::get('/hr', [AdminReportController::class, 'hr'])
                ->middleware('permission:reports.hr')
                ->name('hr');
            Route::get('/planning', [AdminReportController::class, 'planning'])
                ->middleware('permission:reports.planning')
                ->name('planning');
            Route::get('/documents', [AdminReportController::class, 'documents'])
                ->middleware('permission:reports.documents')
                ->name('documents');
            Route::get('/export', AdminReportExportController::class)
                ->middleware('permission:reports.export')
                ->name('export');
        });

        // HR Routes
        require __DIR__.'/admin/hr.php';

        // Planning Routes
        require __DIR__.'/admin/planning.php';

        // Settings Routes
        require __DIR__.'/admin/settings.php';
    });

Route::middleware(['auth'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {
        Route::get('/', PortalDashboardController::class)->name('dashboard');
        Route::get('/more', fn () => Inertia::render('Portal/More/Index'))->name('more.index');

        Route::resource('tickets', PortalTicketController::class)
            ->only(['index', 'create', 'store', 'show']);
        Route::post('tickets/{ticket}/comments', [PortalTicketCommentController::class, 'store'])->name('tickets.comments.store');
        Route::post('tickets/{ticket}/attachments', [PortalTicketAttachmentController::class, 'store'])->name('tickets.attachments.store');
        Route::get('attachments/{attachment}/download', PortalAttachmentDownloadController::class)->name('attachments.download');

        Route::resource('events', PortalEventController::class)
            ->only(['index', 'show']);

        Route::resource('documents', PortalDocumentController::class)
            ->only(['index', 'show']);
        Route::get('documents/{document}/download', PortalDocumentDownloadController::class)->name('documents.download');

        Route::resource('meeting-minutes', PortalMeetingMinuteController::class)
            ->only(['index', 'show'])
            ->parameters(['meeting-minutes' => 'meetingMinute']);

        Route::resource('spaces', PortalSpaceController::class)
            ->only(['index', 'show']);

        Route::resource('space-reservations', PortalSpaceReservationController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['space-reservations' => 'spaceReservation']);
        Route::post('space-reservations/{spaceReservation}/cancel', PortalSpaceReservationCancellationController::class)->name('space-reservations.cancel');

        Route::resource('operational-plans', PortalOperationalPlanController::class)
            ->only(['index', 'show'])
            ->parameters(['operational-plans' => 'operationalPlan']);
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
