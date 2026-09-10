<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\PermissionController;
use Modules\Identity\Http\Controllers\RoleController;
use Modules\Identity\Http\Controllers\UserController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Users - platform staff only (Super Admin/Admin); permission-gated
    Route::middleware(['role:Super Admin,Admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'edit'])->names('users')->middleware('permission:users.*');
        Route::get('/dataTable/users', [UserController::class, 'dataTable'])->name('users.dataTable')->middleware('permission:users.view');
    });

    // Roles - requires Super Admin only
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::resource('roles', RoleController::class)->except(['create', 'edit'])->names('roles')->middleware('permission:roles.*');
        Route::get('/dataTable/roles', [RoleController::class, 'dataTable'])->name('roles.dataTable')->middleware('permission:roles.view');
    });

    // Permissions - requires Super Admin only
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::resource('permissions', PermissionController::class)->except(['create', 'edit'])->names('permissions')->middleware('permission:permissions.*');
        Route::get('/dataTable/permissions', [PermissionController::class, 'dataTable'])->name('permissions.dataTable')->middleware('permission:permissions.view');
    });
});
