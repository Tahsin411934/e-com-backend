<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\PermissionController;
use Modules\Identity\Http\Controllers\RoleController;
use Modules\Identity\Http\Controllers\UserController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Users - requires Super Admin or Admin role
    Route::middleware(['role:Super Admin,Admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'edit'])->names('users');
        Route::get('/dataTable/users', [UserController::class, 'dataTable'])->name('users.dataTable');
    });

    // Roles - requires Super Admin only
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::resource('roles', RoleController::class)->except(['create', 'edit'])->names('roles');
        Route::get('/dataTable/roles', [RoleController::class, 'dataTable'])->name('roles.dataTable');
    });

    // Permissions - requires Super Admin only
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::resource('permissions', PermissionController::class)->except(['create', 'edit'])->names('permissions');
        Route::get('/dataTable/permissions', [PermissionController::class, 'dataTable'])->name('permissions.dataTable');
    });
});
