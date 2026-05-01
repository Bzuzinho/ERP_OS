<?php

use App\Http\Controllers\Admin\OperationalPlanApprovalController;
use App\Http\Controllers\Admin\OperationalPlanCancellationController;
use App\Http\Controllers\Admin\OperationalPlanCompletionController;
use App\Http\Controllers\Admin\OperationalPlanController;
use App\Http\Controllers\Admin\OperationalPlanParticipantController;
use App\Http\Controllers\Admin\OperationalPlanResourceController;
use App\Http\Controllers\Admin\OperationalPlanStatusController;
use App\Http\Controllers\Admin\OperationalPlanTaskController;
use App\Http\Controllers\Admin\RecurringOperationController;
use App\Http\Controllers\Admin\RecurringOperationRunController;
use App\Http\Controllers\Admin\RecurringOperationStatusController;
use Illuminate\Support\Facades\Route;

Route::get('planning', fn () => to_route('admin.operational-plans.index'))->name('planning.index');

Route::resource('operational-plans', OperationalPlanController::class);
Route::patch('operational-plans/{operationalPlan}/status', [OperationalPlanStatusController::class, 'update'])->name('operational-plans.status.update');
Route::post('operational-plans/{operationalPlan}/approve', OperationalPlanApprovalController::class)->name('operational-plans.approve');
Route::post('operational-plans/{operationalPlan}/cancel', OperationalPlanCancellationController::class)->name('operational-plans.cancel');
Route::post('operational-plans/{operationalPlan}/complete', OperationalPlanCompletionController::class)->name('operational-plans.complete');

Route::post('operational-plans/{operationalPlan}/tasks', [OperationalPlanTaskController::class, 'store'])->name('operational-plans.tasks.store');
Route::delete('operational-plans/{operationalPlan}/tasks/{task}', [OperationalPlanTaskController::class, 'destroy'])->name('operational-plans.tasks.destroy');
Route::post('operational-plans/{operationalPlan}/tasks/generate', [OperationalPlanTaskController::class, 'generate'])->name('operational-plans.tasks.generate');

Route::post('operational-plans/{operationalPlan}/participants', [OperationalPlanParticipantController::class, 'store'])->name('operational-plans.participants.store');
Route::delete('operational-plans/{operationalPlan}/participants/{participant}', [OperationalPlanParticipantController::class, 'destroy'])->name('operational-plans.participants.destroy');

Route::post('operational-plans/{operationalPlan}/resources', [OperationalPlanResourceController::class, 'store'])->name('operational-plans.resources.store');
Route::delete('operational-plans/{operationalPlan}/resources/{resource}', [OperationalPlanResourceController::class, 'destroy'])->name('operational-plans.resources.destroy');

Route::resource('recurring-operations', RecurringOperationController::class);
Route::post('recurring-operations/{recurringOperation}/pause', [RecurringOperationStatusController::class, 'pause'])->name('recurring-operations.pause');
Route::post('recurring-operations/{recurringOperation}/resume', [RecurringOperationStatusController::class, 'resume'])->name('recurring-operations.resume');
Route::post('recurring-operations/{recurringOperation}/cancel', [RecurringOperationStatusController::class, 'cancel'])->name('recurring-operations.cancel');
Route::post('recurring-operations/{recurringOperation}/runs', [RecurringOperationRunController::class, 'store'])->name('recurring-operations.runs.store');
Route::post('recurring-operations/{recurringOperation}/runs/{run}/execute', [RecurringOperationRunController::class, 'execute'])->name('recurring-operations.runs.execute');
