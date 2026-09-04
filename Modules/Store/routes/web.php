<?php

use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\AddressController;
use Modules\Store\Http\Controllers\AppSettingController;
use Modules\Store\Http\Controllers\CountryController;
use Modules\Store\Http\Controllers\StoreController;
use Modules\Store\Http\Controllers\StoreOwnerRegisterController;
use Modules\Store\Http\Controllers\StoreStaffController;

/*
|--------------------------------------------------------------------------
| Public market registration - store owners
|--------------------------------------------------------------------------
| The storefront links to this page ("Create Store"). Registration happens
| entirely on this backend; after it the owner is signed into the panel.
*/
Route::middleware('guest')->group(function () {
    Route::get('/register/store-owner', [StoreOwnerRegisterController::class, 'create'])
        ->name('store-owner.register');
    Route::post('/register/store-owner', [StoreOwnerRegisterController::class, 'store'])
        ->name('store-owner.register.post');
});

Route::middleware(['auth', 'verified', 'admin', 'role:Super Admin,Admin'])->group(function () {
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
