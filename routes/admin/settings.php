<?php

use App\Http\Controllers\Admin\Settings\RoleController;
use App\Http\Controllers\Admin\Settings\SettingsController;
use App\Http\Controllers\Admin\Settings\UserController;
use App\Http\Controllers\Admin\Settings\UserPasswordController;
use App\Http\Controllers\Admin\Settings\UserStatusController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->name('settings.')->group(function () {

    Route::get('/', [SettingsController::class, 'index'])->name('index');

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');

        // Roles
        Route::post('/{user}/roles', [UserController::class, 'updateRoles'])->name('update-roles');

        // Status
        Route::post('/{user}/activate', [UserStatusController::class, 'activate'])->name('activate');
        Route::post('/{user}/deactivate', [UserStatusController::class, 'deactivate'])->name('deactivate');

        // Password
        Route::post('/{user}/reset-password', [UserPasswordController::class, 'reset'])->name('reset-password');
    });

    // Roles
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/{role}', [RoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
    });
});
