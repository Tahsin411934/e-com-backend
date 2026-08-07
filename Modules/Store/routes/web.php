<?php

use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\StoreController;
use Modules\Store\Http\Controllers\StoreStaffController;
use Modules\Store\Http\Controllers\CountryController;
use Modules\Store\Http\Controllers\AddressController;
use Modules\Store\Http\Controllers\AppSettingController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Stores
    Route::resource('stores', StoreController::class)->except(['create', 'edit'])->names('stores');
    Route::get('/dataTable/stores', [StoreController::class, 'dataTable'])->name('stores.dataTable');

    // Store Staff
    Route::resource('store-staff', StoreStaffController::class)->except(['create', 'edit'])->names('store-staff');
    Route::get('/dataTable/store-staff', [StoreStaffController::class, 'dataTable'])->name('store-staff.dataTable');

    // Countries
    Route::resource('countries', CountryController::class)->except(['create', 'edit'])->names('countries');
    Route::get('/dataTable/countries', [CountryController::class, 'dataTable'])->name('countries.dataTable');

    // Addresses
    Route::resource('addresses', AddressController::class)->except(['create', 'edit'])->names('addresses');
    Route::get('/dataTable/addresses', [AddressController::class, 'dataTable'])->name('addresses.dataTable');

    // App Settings
    Route::resource('app-settings', AppSettingController::class)->except(['create', 'edit'])->names('app-settings');
    Route::get('/dataTable/app-settings', [AppSettingController::class, 'dataTable'])->name('app-settings.dataTable');
});