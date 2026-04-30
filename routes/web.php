<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\EventParticipantController as AdminEventParticipantController;
use App\Http\Controllers\Admin\EventStatusController as AdminEventStatusController;
use App\Http\Controllers\Admin\TaskChecklistController as AdminTaskChecklistController;
use App\Http\Controllers\Admin\TaskChecklistItemController as AdminTaskChecklistItemController;
use App\Http\Controllers\Admin\TaskController as AdminTaskController;
use App\Http\Controllers\Admin\TaskStatusController as AdminTaskStatusController;
use App\Http\Controllers\Admin\TicketAttachmentController as AdminTicketAttachmentController;
use App\Http\Controllers\Admin\TicketCommentController as AdminTicketCommentController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\TicketStatusController as AdminTicketStatusController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Portal\EventController as PortalEventController;
use App\Http\Controllers\Portal\TicketAttachmentController as PortalTicketAttachmentController;
use App\Http\Controllers\Portal\TicketCommentController as PortalTicketCommentController;
use App\Http\Controllers\Portal\TicketController as PortalTicketController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
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

        Route::resource('contacts', AdminContactController::class);

        Route::resource('tickets', AdminTicketController::class);
        Route::patch('tickets/{ticket}/status', [AdminTicketStatusController::class, 'update'])->name('tickets.status.update');
        Route::patch('tickets/{ticket}/assign', [AdminTicketController::class, 'assign'])->name('tickets.assign');
        Route::post('tickets/{ticket}/comments', [AdminTicketCommentController::class, 'store'])->name('tickets.comments.store');
        Route::post('tickets/{ticket}/attachments', [AdminTicketAttachmentController::class, 'store'])->name('tickets.attachments.store');

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
    });

Route::middleware(['auth'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {
        Route::get('/', PortalDashboardController::class)->name('dashboard');

        Route::resource('tickets', PortalTicketController::class)
            ->only(['index', 'create', 'store', 'show']);
        Route::post('tickets/{ticket}/comments', [PortalTicketCommentController::class, 'store'])->name('tickets.comments.store');
        Route::post('tickets/{ticket}/attachments', [PortalTicketAttachmentController::class, 'store'])->name('tickets.attachments.store');

        Route::resource('events', PortalEventController::class)
            ->only(['index', 'show']);
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
