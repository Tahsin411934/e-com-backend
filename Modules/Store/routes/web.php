<?php

use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\AddressController;
use Modules\Store\Http\Controllers\AppSettingController;
use Modules\Store\Http\Controllers\CountryController;
use Modules\Store\Http\Controllers\StoreController;
use Modules\Store\Http\Controllers\StoreStaffController;
use Modules\Store\Http\Controllers\StoreRoleController;
use Modules\Store\Http\Controllers\PlanController;
use Modules\Store\Http\Controllers\FeatureController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Stores
    Route::resource('stores', StoreController::class)->except(['create', 'edit'])->names('stores')->middleware('permission:stores.*');
    Route::get('/dataTable/stores', [StoreController::class, 'dataTable'])->name('stores.dataTable')->middleware('permission:stores.view');

    // Store Staff
    Route::resource('store-staff', StoreStaffController::class)->except(['create', 'edit'])->names('store-staff')->middleware('permission:store-staff.*');
    Route::get('/dataTable/store-staff', [StoreStaffController::class, 'dataTable'])->name('store-staff.dataTable')->middleware('permission:store-staff.view');

    Route::resource('store-roles', StoreRoleController::class)->except(['create', 'edit'])->names('store-roles')->middleware('permission:store-roles.*');
    Route::get('/dataTable/store-roles', [StoreRoleController::class, 'dataTable'])->name('store-roles.dataTable')->middleware('permission:store-roles.view');

    // Countries
    Route::resource('countries', CountryController::class)->except(['create', 'edit'])->names('countries')->middleware('permission:countries.*');
    Route::get('/dataTable/countries', [CountryController::class, 'dataTable'])->name('countries.dataTable')->middleware('permission:countries.view');

    // Addresses
    Route::resource('addresses', AddressController::class)->except(['create', 'edit'])->names('addresses')->middleware('permission:addresses.*');
    Route::get('/dataTable/addresses', [AddressController::class, 'dataTable'])->name('addresses.dataTable')->middleware('permission:addresses.view');

    // App Settings
    Route::resource('app-settings', AppSettingController::class)->except(['create', 'edit'])->names('app-settings')->middleware('permission:app-settings.*');
    Route::get('/dataTable/app-settings', [AppSettingController::class, 'dataTable'])->name('app-settings.dataTable')->middleware('permission:app-settings.view');
    Route::get('/plans', [PlanController::class, 'index'])->name('plans.index')->middleware('permission:plans.view');
    Route::get('/dataTable/plans', [PlanController::class, 'dataTable'])->name('plans.dataTable')->middleware('permission:plans.view');
    Route::post('/plans', [PlanController::class, 'store'])->name('plans.store')->middleware('permission:plans.create');
    Route::get('/plans/{plan}', [PlanController::class, 'show'])->name('plans.show')->middleware('permission:plans.view');
    Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update')->middleware('permission:plans.edit');
    Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy')->middleware('permission:plans.delete');
    Route::get('/features', [FeatureController::class, 'index'])->name('features.index')->middleware('permission:features.view');
    Route::get('/dataTable/features', [FeatureController::class, 'dataTable'])->name('features.dataTable')->middleware('permission:features.view');
    Route::post('/features', [FeatureController::class, 'store'])->name('features.store')->middleware('permission:features.create');
    Route::get('/features/{feature}', [FeatureController::class, 'show'])->name('features.show')->middleware('permission:features.view');
    Route::put('/features/{feature}', [FeatureController::class, 'update'])->name('features.update')->middleware('permission:features.edit');
    Route::delete('/features/{feature}', [FeatureController::class, 'destroy'])->name('features.destroy')->middleware('permission:features.delete');
});
