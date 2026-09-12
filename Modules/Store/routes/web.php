<?php

use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\AddressController;
use Modules\Store\Http\Controllers\AppSettingController;
use Modules\Store\Http\Controllers\CountryController;
use Modules\Store\Http\Controllers\StoreController;
use Modules\Store\Http\Controllers\StoreStaffController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Stores
    Route::resource('stores', StoreController::class)->except(['create', 'edit'])->names('stores')->middleware('permission:stores.*');
    Route::get('/dataTable/stores', [StoreController::class, 'dataTable'])->name('stores.dataTable')->middleware('permission:stores.view');

    // Store Staff
    Route::resource('store-staff', StoreStaffController::class)->except(['create', 'edit'])->names('store-staff')->middleware('permission:store-staff.*');
    Route::get('/dataTable/store-staff', [StoreStaffController::class, 'dataTable'])->name('store-staff.dataTable')->middleware('permission:store-staff.view');

    // Countries
    Route::resource('countries', CountryController::class)->except(['create', 'edit'])->names('countries')->middleware('permission:countries.*');
    Route::get('/dataTable/countries', [CountryController::class, 'dataTable'])->name('countries.dataTable')->middleware('permission:countries.view');

    // Addresses
    Route::resource('addresses', AddressController::class)->except(['create', 'edit'])->names('addresses')->middleware('permission:addresses.*');
    Route::get('/dataTable/addresses', [AddressController::class, 'dataTable'])->name('addresses.dataTable')->middleware('permission:addresses.view');

    // App Settings
    Route::resource('app-settings', AppSettingController::class)->except(['create', 'edit'])->names('app-settings')->middleware('permission:app-settings.*');
    Route::get('/dataTable/app-settings', [AppSettingController::class, 'dataTable'])->name('app-settings.dataTable')->middleware('permission:app-settings.view');
});
