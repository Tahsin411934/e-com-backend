<?php

use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\StoreAuthController;

// Public SaaS registration for store owners (tenants)
Route::post('/v1/register/store-owner', [StoreAuthController::class, 'register'])
    ->name('api.store.register-owner');

// Authenticated store context (platform staff may inspect, owners read their own store)
Route::middleware(['convert.auth.cookie', 'auth:sanctum', 'role:Super Admin,Admin,Store Owner'])
    ->prefix('v1')
    ->group(function () {
        Route::get('/store', [StoreAuthController::class, 'myStore'])->name('api.store.my-store');
    });
