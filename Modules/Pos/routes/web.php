<?php

use Illuminate\Support\Facades\Route;
use Modules\Pos\Http\Controllers\PosRegisterController;
use Modules\Pos\Http\Controllers\PosSaleController;
use Modules\Pos\Http\Controllers\PosSellController;
use Modules\Pos\Http\Controllers\PosShiftController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // POS Registers
    Route::resource('pos-registers', PosRegisterController::class)->except(['create', 'edit'])->names('pos-registers')->middleware('permission:pos-registers.*');
    Route::get('/dataTable/pos-registers', [PosRegisterController::class, 'dataTable'])->name('pos-registers.dataTable')->middleware('permission:pos-registers.view');

    // POS Shifts
    Route::resource('pos-shifts', PosShiftController::class)->except(['create', 'edit'])->names('pos-shifts')->middleware('permission:pos-shifts.*');
    Route::get('/dataTable/pos-shifts', [PosShiftController::class, 'dataTable'])->name('pos-shifts.dataTable')->middleware('permission:pos-shifts.view');
    Route::post('/pos-shifts/{id}/close', [PosShiftController::class, 'closeShift'])->name('pos-shifts.close')->middleware('permission:pos-shifts.edit');

    // POS Sales (CRUD)
    Route::resource('pos-sales', PosSaleController::class)->except(['create', 'edit'])->names('pos-sales')->middleware('permission:pos-sales.*');
    Route::get('/dataTable/pos-sales', [PosSaleController::class, 'dataTable'])->name('pos-sales.dataTable')->middleware('permission:pos-sales.view');
    Route::post('/pos-sales/{id}/void', [PosSaleController::class, 'voidSale'])->name('pos-sales.void')->middleware('permission:pos-sales.delete');

    // POS Create Sell (New Interface)
    Route::get('/pos-sell', [PosSellController::class, 'index'])->name('pos.sell.index')->middleware('permission:pos.sell');
    Route::get('/pos-sell/search-customers', [PosSellController::class, 'searchCustomers'])->name('pos.sell.search-customers')->middleware('permission:pos.sell');
    Route::get('/pos-sell/search-products', [PosSellController::class, 'searchProducts'])->name('pos.sell.search-products')->middleware('permission:pos.sell');
    Route::post('/pos-sell/process', [PosSellController::class, 'processSale'])->name('pos.sell.process')->middleware('permission:pos.sell');
    Route::get('/pos-sell/recent-sales', [PosSellController::class, 'getRecentSales'])->name('pos.sell.recent-sales')->middleware('permission:pos.sell');
});
