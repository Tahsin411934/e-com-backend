<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\AuthController;
use Modules\Identity\Http\Controllers\IdentityController;
use Modules\Identity\Http\Controllers\PermissionController;
use Modules\Identity\Http\Controllers\RoleController;
use Modules\Identity\Http\Controllers\UserController;
use Modules\Identity\Http\Controllers\CustomerProfileController;
use Modules\Identity\Http\Controllers\CentralCustomerAuthController;
use Modules\Identity\Http\Middleware\EnsureCentralCustomer;
use Modules\Identity\Http\Controllers\CentralCustomerCommerceController;
use Modules\Identity\Http\Controllers\CentralCustomerOrderController;

Route::prefix('v1/central')->group(function () {
    Route::post('guest-checkout', [CentralCustomerCommerceController::class, 'guestCheckout'])->middleware('throttle:10,1');
    Route::get('products/{productId}/reviews', [CentralCustomerCommerceController::class, 'reviews']);
    Route::middleware(['auth:sanctum', EnsureCentralCustomer::class])->group(function () {
        Route::post('carts/sync', [CentralCustomerCommerceController::class, 'syncCart']);
        Route::post('checkout', [CentralCustomerCommerceController::class, 'checkout']);
        Route::get('wishlists', [CentralCustomerCommerceController::class, 'wishlist']);
        Route::post('wishlists/toggle', [CentralCustomerCommerceController::class, 'toggleWishlist']);
        Route::delete('wishlists/{productId}', [CentralCustomerCommerceController::class, 'removeWishlist']);
        Route::post('products/{productId}/reviews', [CentralCustomerCommerceController::class, 'submitReview']);
        Route::get('orders', [CentralCustomerOrderController::class, 'index']);
        Route::get('orders/{order}', [CentralCustomerOrderController::class, 'show']);
    });
});

Route::prefix('v1/central/auth')->group(function () {
    Route::post('register', [CentralCustomerAuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('login', [CentralCustomerAuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('forgot-password', [CentralCustomerAuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('reset-password', [CentralCustomerAuthController::class, 'resetPassword'])->middleware('throttle:5,1');
    Route::middleware(['auth:sanctum', EnsureCentralCustomer::class])->group(function () {
        Route::get('me', [CentralCustomerAuthController::class, 'me']);
        Route::post('logout', [CentralCustomerAuthController::class, 'logout']);
        Route::post('logout-all', [CentralCustomerAuthController::class, 'logoutAll']);
        Route::post('refresh', [CentralCustomerAuthController::class, 'refresh']);
        Route::post('change-password', [CentralCustomerAuthController::class, 'changePassword']);
    });
});

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
