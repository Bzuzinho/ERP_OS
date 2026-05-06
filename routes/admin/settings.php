<?php

use App\Http\Controllers\Admin\Settings\OrganizationController;
use App\Http\Controllers\Admin\Settings\RoleController;
use App\Http\Controllers\Admin\Settings\SettingsController;
use App\Http\Controllers\Admin\Settings\UserController;
use App\Http\Controllers\Admin\Settings\UserPasswordController;
use App\Http\Controllers\Admin\Settings\UserStatusController;
use App\Http\Controllers\Admin\ServiceAreaController;
use App\Http\Controllers\Admin\ServiceAreaUserController;
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

        // Avatar
        Route::post('/{user}/avatar', [UserController::class, 'updateAvatar'])->name('update-avatar');
    });

    // Roles
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/{role}', [RoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
    });

    // Organization
    Route::prefix('organization')->name('organization.')->group(function () {
        Route::get('/edit', [OrganizationController::class, 'edit'])->name('edit');
        Route::put('/', [OrganizationController::class, 'update'])->name('update');
        Route::post('/logo', [OrganizationController::class, 'updateLogo'])->name('update-logo');
    });

    // Service Areas (as part of Settings module)
    Route::prefix('service-areas')->name('service-areas.')->group(function () {
        Route::get('/', [ServiceAreaController::class, 'index'])->name('index');
        Route::get('/create', [ServiceAreaController::class, 'create'])->name('create');
        Route::post('/', [ServiceAreaController::class, 'store'])->name('store');
        Route::get('/{serviceArea}', [ServiceAreaController::class, 'show'])->name('show');
        Route::get('/{serviceArea}/edit', [ServiceAreaController::class, 'edit'])->name('edit');
        Route::put('/{serviceArea}', [ServiceAreaController::class, 'update'])->name('update');
        Route::delete('/{serviceArea}', [ServiceAreaController::class, 'destroy'])->name('destroy');

        // Service Area Users
        Route::post('/{serviceArea}/users', [ServiceAreaUserController::class, 'store'])->name('users.store');
        Route::delete('/{serviceArea}/users/{userId}', [ServiceAreaUserController::class, 'destroy'])->name('users.destroy');
    });
});
