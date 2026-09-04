<?php

use Illuminate\Support\Facades\Route;
use Modules\Pos\Http\Controllers\PosRegisterController;
use Modules\Pos\Http\Controllers\PosSaleController;
use Modules\Pos\Http\Controllers\PosSellController;
use Modules\Pos\Http\Controllers\PosShiftController;

Route::middleware(['auth', 'verified', 'admin', 'role:Super Admin,Admin,Manager,Staff'])->group(function () {
    // POS Registers
    Route::resource('pos-registers', PosRegisterController::class)->except(['create', 'edit'])->names('pos-registers');
    Route::get('/dataTable/pos-registers', [PosRegisterController::class, 'dataTable'])->name('pos-registers.dataTable');

    // POS Shifts
    Route::resource('pos-shifts', PosShiftController::class)->except(['create', 'edit'])->names('pos-shifts');
    Route::get('/dataTable/pos-shifts', [PosShiftController::class, 'dataTable'])->name('pos-shifts.dataTable');
    Route::post('/pos-shifts/{id}/close', [PosShiftController::class, 'closeShift'])->name('pos-shifts.close');

    // POS Sales (CRUD)
    Route::resource('pos-sales', PosSaleController::class)->except(['create', 'edit'])->names('pos-sales');
    Route::get('/dataTable/pos-sales', [PosSaleController::class, 'dataTable'])->name('pos-sales.dataTable');
    Route::post('/pos-sales/{id}/void', [PosSaleController::class, 'voidSale'])->name('pos-sales.void');

    // POS Create Sell (New Interface)
    Route::get('/pos-sell', [PosSellController::class, 'index'])->name('pos.sell.index');
    Route::get('/pos-sell/search-customers', [PosSellController::class, 'searchCustomers'])->name('pos.sell.search-customers');
    Route::get('/pos-sell/search-products', [PosSellController::class, 'searchProducts'])->name('pos.sell.search-products');
    Route::post('/pos-sell/process', [PosSellController::class, 'processSale'])->name('pos.sell.process');
    Route::get('/pos-sell/recent-sales', [PosSellController::class, 'getRecentSales'])->name('pos.sell.recent-sales');
});
