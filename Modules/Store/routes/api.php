<?php

use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\StoreAuthController;
use Modules\Store\Http\Controllers\StoreDomainController;
use Modules\Store\Http\Controllers\StoreEmailVerificationController;
use Modules\Store\Http\Controllers\PlanController;

// Public SaaS registration for store owners (tenants)
Route::get('/v1/register/store-slug-availability', [StoreAuthController::class, 'slugAvailability'])
    ->name('api.store.register-slug-availability');
Route::post('/v1/register/store-owner', [StoreAuthController::class, 'register'])
    ->middleware('throttle:5,1')
    ->name('api.store.register-owner');
Route::get('/v1/plans', [PlanController::class, 'publicIndex'])->name('store.plans');
Route::get('/v1/email/verify/{id}/{hash}', [StoreEmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('store.email.verify');
Route::post('/v1/email/verification-notification', [StoreEmailVerificationController::class, 'resend'])
    ->middleware('throttle:3,10')
    ->name('store.email.resend');

// Authenticated store context (platform staff may inspect, owners read their own store)
Route::middleware(['convert.auth.cookie', 'auth:sanctum', 'role:Super Admin,Admin,Store Owner'])
    ->prefix('v1')
    ->group(function () {
        Route::get('/store', [StoreAuthController::class, 'myStore'])->name('api.store.my-store');
    });

// Storefront domain management — the authenticated owner manages the
// hostnames of their own store (free subdomain + custom domains).
Route::middleware(['auth', 'convert.auth.cookie', 'auth:sanctum'])
    ->prefix('v1')
    ->group(function () {
    Route::get('/store/domains', [StoreDomainController::class, 'index'])->name('api.store.domains.index');
        Route::post('/store/domains', [StoreDomainController::class, 'store'])->middleware('throttle:10,1')->name('api.store.domains.store');
        Route::post('/store/domains/{storeDomain}/verify', [StoreDomainController::class, 'verify'])->middleware('throttle:10,1')->name('api.store.domains.verify');
        Route::post('/store/domains/{storeDomain}/primary', [StoreDomainController::class, 'primary'])->name('api.store.domains.primary');
        Route::delete('/store/domains/{storeDomain}', [StoreDomainController::class, 'destroy'])->name('api.store.domains.destroy');
    });
