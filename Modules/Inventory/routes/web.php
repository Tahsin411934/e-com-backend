<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryLocationController;
use Modules\Inventory\Http\Controllers\InventoryMovementController;
use Modules\Inventory\Http\Controllers\InventoryStockController;
use Modules\Inventory\Http\Controllers\PurchaseOrderController;
use Modules\Inventory\Http\Controllers\PurchaseReturnController;
use Modules\Inventory\Http\Controllers\SupplierController;
use Modules\Inventory\Http\Controllers\SupplierPaymentController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Inventory Locations
    Route::get('inventory-locations', [InventoryLocationController::class, 'index'])
        ->name('inventory-locations.index')->middleware('permission:inventory-locations.view');
    Route::get('dataTable/inventory-locations', [InventoryLocationController::class, 'dataTable'])
        ->name('inventory-locations.dataTable')->middleware('permission:inventory-locations.view');
    Route::get('inventory-locations/{id}', [InventoryLocationController::class, 'show'])
        ->name('inventory-locations.show')->middleware('permission:inventory-locations.view');
    Route::post('inventory-locations', [InventoryLocationController::class, 'store'])
        ->name('inventory-locations.store')->middleware('permission:inventory-locations.create');
    Route::put('inventory-locations/{id}', [InventoryLocationController::class, 'update'])
        ->name('inventory-locations.update')->middleware('permission:inventory-locations.edit');
    Route::patch('inventory-locations/{id}', [InventoryLocationController::class, 'update'])
        ->middleware('permission:inventory-locations.edit');
    Route::delete('inventory-locations/{id}', [InventoryLocationController::class, 'destroy'])
        ->name('inventory-locations.destroy')->middleware('permission:inventory-locations.delete');

    // Inventory Stock
    Route::get('inventory-stock', [InventoryStockController::class, 'index'])
        ->name('inventory-stock.index')->middleware('permission:inventory-stock.view');
    Route::get('dataTable/inventory-stock', [InventoryStockController::class, 'dataTable'])
        ->name('inventory-stock.dataTable')->middleware('permission:inventory-stock.view');
    Route::get('inventory-stock/{id}', [InventoryStockController::class, 'show'])
        ->name('inventory-stock.show')->middleware('permission:inventory-stock.view');
    Route::post('inventory-stock', [InventoryStockController::class, 'store'])
        ->name('inventory-stock.store')->middleware('permission:inventory-stock.create');
    Route::put('inventory-stock/{id}', [InventoryStockController::class, 'update'])
        ->name('inventory-stock.update')->middleware('permission:inventory-stock.edit');
    Route::patch('inventory-stock/{id}', [InventoryStockController::class, 'update'])
        ->middleware('permission:inventory-stock.edit');
    Route::delete('inventory-stock/{id}', [InventoryStockController::class, 'destroy'])
        ->name('inventory-stock.destroy')->middleware('permission:inventory-stock.delete');

    // Inventory Movements
    Route::get('inventory-movements', [InventoryMovementController::class, 'index'])
        ->name('inventory-movements.index')->middleware('permission:inventory-movements.view');
    Route::get('dataTable/inventory-movements', [InventoryMovementController::class, 'dataTable'])
        ->name('inventory-movements.dataTable')->middleware('permission:inventory-movements.view');
    Route::get('inventory-movements/{id}', [InventoryMovementController::class, 'show'])
        ->name('inventory-movements.show')->middleware('permission:inventory-movements.view');
    Route::post('inventory-movements', [InventoryMovementController::class, 'store'])
        ->name('inventory-movements.store')->middleware('permission:inventory-movements.create');
    Route::put('inventory-movements/{id}', [InventoryMovementController::class, 'update'])
        ->name('inventory-movements.update')->middleware('permission:inventory-movements.edit');
    Route::patch('inventory-movements/{id}', [InventoryMovementController::class, 'update'])
        ->middleware('permission:inventory-movements.edit');
    Route::delete('inventory-movements/{id}', [InventoryMovementController::class, 'destroy'])
        ->name('inventory-movements.destroy')->middleware('permission:inventory-movements.delete');

    // Suppliers
    Route::get('suppliers', [SupplierController::class, 'index'])
        ->name('suppliers.index')->middleware('permission:suppliers.view');
    Route::get('dataTable/suppliers', [SupplierController::class, 'dataTable'])
        ->name('suppliers.dataTable')->middleware('permission:suppliers.view');
    Route::get('suppliers/{id}', [SupplierController::class, 'show'])
        ->name('suppliers.show')->middleware('permission:suppliers.view');
    Route::post('suppliers', [SupplierController::class, 'store'])
        ->name('suppliers.store')->middleware('permission:suppliers.create');
    Route::put('suppliers/{id}', [SupplierController::class, 'update'])
        ->name('suppliers.update')->middleware('permission:suppliers.edit');
    Route::patch('suppliers/{id}', [SupplierController::class, 'update'])
        ->middleware('permission:suppliers.edit');
    Route::delete('suppliers/{id}', [SupplierController::class, 'destroy'])
        ->name('suppliers.destroy')->middleware('permission:suppliers.delete');

    // Purchase Orders
    Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])
        ->name('purchase-orders.index')->middleware('permission:purchase-orders.view');
    Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])
        ->name('purchase-orders.create')->middleware('permission:purchase-orders.create');
    Route::get('purchase-orders-search-products', [PurchaseOrderController::class, 'searchProducts'])
        ->name('purchase-orders.search-products')->middleware('permission:purchase-orders.view');
    Route::get('dataTable/purchase-orders', [PurchaseOrderController::class, 'dataTable'])
        ->name('purchase-orders.dataTable')->middleware('permission:purchase-orders.view');
    Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])
        ->name('purchase-orders.store')->middleware('permission:purchase-orders.create');
    Route::get('purchase-orders/{id}', [PurchaseOrderController::class, 'show'])
        ->name('purchase-orders.show')->middleware('permission:purchase-orders.view');
    Route::get('purchase-orders/{id}/edit', [PurchaseOrderController::class, 'edit'])
        ->name('purchase-orders.edit')->middleware('permission:purchase-orders.edit');
    Route::post('purchase-orders/{id}/update-status', [PurchaseOrderController::class, 'updateStatus'])
        ->name('purchase-orders.update-status')->middleware('permission:purchase-orders.edit');
    Route::put('purchase-orders/{id}', [PurchaseOrderController::class, 'update'])
        ->name('purchase-orders.update')->middleware('permission:purchase-orders.edit');
    Route::patch('purchase-orders/{id}', [PurchaseOrderController::class, 'update'])
        ->middleware('permission:purchase-orders.edit');
    Route::delete('purchase-orders/{id}', [PurchaseOrderController::class, 'destroy'])
        ->name('purchase-orders.destroy')->middleware('permission:purchase-orders.delete');

    // Purchase Returns
    Route::get('purchase-returns', [PurchaseReturnController::class, 'index'])
        ->name('purchase-returns.index')->middleware('permission:purchase-returns.view');
    Route::get('purchase-returns/create', [PurchaseReturnController::class, 'create'])
        ->name('purchase-returns.create')->middleware('permission:purchase-returns.create');
    Route::get('dataTable/purchase-returns', [PurchaseReturnController::class, 'dataTable'])
        ->name('purchase-returns.dataTable')->middleware('permission:purchase-returns.view');
    Route::post('purchase-returns', [PurchaseReturnController::class, 'store'])
        ->name('purchase-returns.store')->middleware('permission:purchase-returns.create');
    Route::get('purchase-returns/{id}', [PurchaseReturnController::class, 'show'])
        ->name('purchase-returns.show')->middleware('permission:purchase-returns.view');
    Route::get('purchase-returns/{id}/edit', [PurchaseReturnController::class, 'edit'])
        ->name('purchase-returns.edit')->middleware('permission:purchase-returns.edit');
    Route::put('purchase-returns/{id}', [PurchaseReturnController::class, 'update'])
        ->name('purchase-returns.update')->middleware('permission:purchase-returns.edit');
    Route::patch('purchase-returns/{id}', [PurchaseReturnController::class, 'update'])
        ->middleware('permission:purchase-returns.edit');
    Route::delete('purchase-returns/{id}', [PurchaseReturnController::class, 'destroy'])
        ->name('purchase-returns.destroy')->middleware('permission:purchase-returns.delete');

    // Supplier Payments (professional payment ledger against POs)
    Route::get('supplier-payments', [SupplierPaymentController::class, 'index'])
        ->name('supplier-payments.index')->middleware('permission:supplier-payments.view');
    Route::get('dataTable/supplier-payments', [SupplierPaymentController::class, 'dataTable'])
        ->name('supplier-payments.dataTable')->middleware('permission:supplier-payments.view');
    Route::post('supplier-payments', [SupplierPaymentController::class, 'store'])
        ->name('supplier-payments.store')->middleware('permission:supplier-payments.create');
    Route::get('supplier-payments/{id}', [SupplierPaymentController::class, 'show'])
        ->name('supplier-payments.show')->middleware('permission:supplier-payments.view');
    Route::delete('supplier-payments/{id}', [SupplierPaymentController::class, 'destroy'])
        ->name('supplier-payments.destroy')->middleware('permission:supplier-payments.delete');
});
