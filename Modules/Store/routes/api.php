<?php

use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\StoreAuthController;
use Modules\Store\Http\Controllers\StoreDomainController;

// Public SaaS registration for store owners (tenants)
Route::post('/v1/register/store-owner', [StoreAuthController::class, 'register'])
    ->name('api.store.register-owner');

// Authenticated store context (platform staff may inspect, owners read their own store)
Route::middleware(['convert.auth.cookie', 'auth:sanctum', 'role:Super Admin,Admin,Store Owner'])
    ->prefix('v1')
    ->group(function () {
        Route::get('/store', [StoreAuthController::class, 'myStore'])->name('api.store.my-store');
    });

// Storefront domain management — the authenticated owner manages the
// hostnames of their own store (free subdomain + custom domains).
Route::middleware(['convert.auth.cookie', 'auth:sanctum'])
    ->prefix('v1')
    ->group(function () {
        Route::get('/store/domains', [StoreDomainController::class, 'index'])->name('api.store.domains.index');
        Route::post('/store/domains', [StoreDomainController::class, 'store'])->name('api.store.domains.store');
        Route::post('/store/domains/{storeDomain}/verify', [StoreDomainController::class, 'verify'])->name('api.store.domains.verify');
        Route::post('/store/domains/{storeDomain}/primary', [StoreDomainController::class, 'primary'])->name('api.store.domains.primary');
        Route::delete('/store/domains/{storeDomain}', [StoreDomainController::class, 'destroy'])->name('api.store.domains.destroy');
    });

