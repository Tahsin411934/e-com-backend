<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\AuthController;
use Modules\Identity\Http\Controllers\IdentityController;
use Modules\Identity\Http\Controllers\PermissionController;
use Modules\Identity\Http\Controllers\RoleController;
use Modules\Identity\Http\Controllers\UserController;
use Modules\Identity\Http\Controllers\CustomerProfileController;

// Public authentication routes
Route::post('/v1/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/v1/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/v1/forgot-password', [AuthController::class, 'forgotPassword'])->name('identity.forgot-password');
Route::post('/v1/reset-password', [AuthController::class, 'resetPassword'])->name('identity.reset-password');

// Protected routes (require authentication)
Route::middleware(['convert.auth.cookie', 'auth:sanctum'])->prefix('v1')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/user', [AuthController::class, 'user'])->name('identity.user');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('identity.refresh');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('identity.change-password');
    Route::get('/customer-profile', [CustomerProfileController::class, 'show'])->name('identity.customer-profile.show');
    Route::put('/customer-profile', [CustomerProfileController::class, 'update'])->name('identity.customer-profile.update');

    // Identity routes
    Route::apiResource('identities', IdentityController::class)->names('identity');

    // User management routes - Super Admin or Admin only
    Route::middleware('role:Super Admin,Admin')->group(function () {
        Route::apiResource('users', UserController::class)->names('users');
    });

    // Role & permission management routes - Super Admin only
    Route::middleware('role:Super Admin')->group(function () {
        Route::apiResource('roles', RoleController::class)->names('roles');
        Route::apiResource('permissions', PermissionController::class)->names('permissions');
    });
});
