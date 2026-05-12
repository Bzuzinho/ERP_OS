<?php

use App\Http\Controllers\Mobile\TodayController;
use App\Http\Controllers\Mobile\MyTasksController;
use App\Http\Controllers\Mobile\MyCalendarController;
use App\Http\Controllers\Mobile\CommunicationsController;
use App\Http\Controllers\Mobile\MoreController;
use App\Http\Controllers\Mobile\TaskAttachmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('mobile')
    ->name('mobile.')
    ->group(function () {
        // Redirect to today
        Route::get('/', fn () => to_route('mobile.today.index'))->name('index');

        // Today view
        Route::get('/hoje', [TodayController::class, 'index'])->name('today.index');

        // My tasks
        Route::get('/tarefas', [MyTasksController::class, 'index'])->name('tasks.index');
        Route::get('/tarefas/{task}', [MyTasksController::class, 'show'])->name('tasks.show');

        // Task actions
        Route::post('/tarefas/{task}/iniciar', [MyTasksController::class, 'start'])->name('tasks.start');
        Route::post('/tarefas/{task}/concluir', [MyTasksController::class, 'complete'])->name('tasks.complete');
        Route::post('/tarefas/{task}/enviar-validacao', [MyTasksController::class, 'submitForValidation'])->name('tasks.submit-validation');
        Route::post('/tarefas/{task}/reabrir', [MyTasksController::class, 'reopen'])->name('tasks.reopen');

        // Task checklists
        Route::post('/tarefas/{task}/checklists/{checklist}', [MyTasksController::class, 'updateChecklistItem'])->name('tasks.checklists.update');

        // Task attachments
        Route::post('/tarefas/{task}/anexos', [TaskAttachmentController::class, 'store'])->name('tasks.attachments.store');
        Route::get('/tarefas/{task}/anexos/{attachment}/download', [TaskAttachmentController::class, 'download'])->name('tasks.attachments.download');

        // Task observations
        Route::post('/tarefas/{task}/observacoes', [MyTasksController::class, 'addObservation'])->name('tasks.observations.store');

        // Calendar
        Route::get('/agenda', [MyCalendarController::class, 'index'])->name('calendar.index');

        // Communications
        Route::get('/comunicacoes', [CommunicationsController::class, 'index'])->name('communications.index');

        // More
        Route::get('/mais', [MoreController::class, 'index'])->name('more.index');
    });
