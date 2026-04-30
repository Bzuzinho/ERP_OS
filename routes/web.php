<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\TicketAttachmentController as AdminTicketAttachmentController;
use App\Http\Controllers\Admin\TicketCommentController as AdminTicketCommentController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\TicketStatusController as AdminTicketStatusController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
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
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
