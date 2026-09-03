<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\AuthController;
use Modules\Identity\Http\Controllers\IdentityController;
use Modules\Identity\Http\Controllers\PermissionController;
use Modules\Identity\Http\Controllers\RoleController;
use Modules\Identity\Http\Controllers\UserController;

// Public authentication routes
Route::post('/v1/register', [AuthController::class, 'register']);
Route::post('/v1/login', [AuthController::class, 'login']);
Route::post('/v1/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/v1/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes (require authentication)
Route::middleware(['convert.auth.cookie', 'auth:sanctum'])->prefix('v1')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

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
