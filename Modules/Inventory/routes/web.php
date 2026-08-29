<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryLocationController;
use Modules\Inventory\Http\Controllers\InventoryMovementController;
use Modules\Inventory\Http\Controllers\InventoryStockController;
use Modules\Inventory\Http\Controllers\PurchaseOrderController;
use Modules\Inventory\Http\Controllers\PurchaseReturnController;
use Modules\Inventory\Http\Controllers\SupplierController;
use Modules\Inventory\Http\Controllers\SupplierPaymentController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Inventory Locations
    Route::resource('inventory-locations', InventoryLocationController::class)->except(['create', 'edit'])->names('inventory-locations');
    Route::get('/dataTable/inventory-locations', [InventoryLocationController::class, 'dataTable'])->name('inventory-locations.dataTable');

    // Inventory Stock
    Route::resource('inventory-stock', InventoryStockController::class)->except(['create', 'edit'])->names('inventory-stock');
    Route::get('/dataTable/inventory-stock', [InventoryStockController::class, 'dataTable'])->name('inventory-stock.dataTable');

    // Inventory Movements
    Route::resource('inventory-movements', InventoryMovementController::class)->except(['create', 'edit'])->names('inventory-movements');
    Route::get('/dataTable/inventory-movements', [InventoryMovementController::class, 'dataTable'])->name('inventory-movements.dataTable');

    // Suppliers
    Route::resource('suppliers', SupplierController::class)->except(['create', 'edit'])->names('suppliers');
    Route::get('/dataTable/suppliers', [SupplierController::class, 'dataTable'])->name('suppliers.dataTable');

    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class)->names('purchase-orders');
    Route::get('/dataTable/purchase-orders', [PurchaseOrderController::class, 'dataTable'])->name('purchase-orders.dataTable');
    Route::post('purchase-orders/{id}/update-status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status');
    Route::get('/purchase-orders-search-products', [PurchaseOrderController::class, 'searchProducts'])->name('purchase-orders.search-products');

    // Purchase Returns
    Route::resource('purchase-returns', PurchaseReturnController::class)->names('purchase-returns');
    Route::get('/dataTable/purchase-returns', [PurchaseReturnController::class, 'dataTable'])->name('purchase-returns.dataTable');

    // Supplier Payments (professional payment ledger against POs)
    Route::resource('supplier-payments', SupplierPaymentController::class)->only(['index', 'store', 'show', 'destroy'])->names('supplier-payments');
    Route::get('/dataTable/supplier-payments', [SupplierPaymentController::class, 'dataTable'])->name('supplier-payments.dataTable');
});
