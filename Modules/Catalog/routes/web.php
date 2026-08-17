<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Http\Controllers\BrandController;
use Modules\Catalog\Http\Controllers\CategoryController;
use Modules\Catalog\Http\Controllers\ProductController;
use Modules\Catalog\Http\Controllers\UnitController;
use Modules\Catalog\Http\Controllers\SizeController;
use Modules\Catalog\Http\Controllers\BarcodePrintController;
use Modules\Catalog\Http\Controllers\TaxRateController;
use Modules\Catalog\Http\Controllers\ProductRequestController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/barcode-print', [BarcodePrintController::class, 'index'])->name('barcode-print.index');
    Route::get('/barcode-print/search', [BarcodePrintController::class, 'search'])->name('barcode-print.search');
    Route::get('/barcode-print/autocomplete', [BarcodePrintController::class, 'autocomplete'])->name('barcode-print.autocomplete');
    Route::get('/barcode-print/variants/{product}', [BarcodePrintController::class, 'variants'])->name('barcode-print.variants');
    Route::post('/barcode-print/print', [BarcodePrintController::class, 'print'])->name('barcode-print.print');
    
    Route::post('/products/reorder', [ProductController::class, 'reorder'])->name('products.reorder');
    Route::post('/products/{id}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
    Route::resource('products', ProductController::class)->names('products');
    Route::get('/dataTable/products', [ProductController::class, 'dataTable'])->name('products.dataTable');
    
    Route::resource('brands', BrandController::class)->except(['create', 'edit'])->names('brands');
    Route::get('/dataTable/brands', [BrandController::class, 'dataTable'])->name('brands.dataTable');

    Route::resource('categories', CategoryController::class)->except(['create', 'edit'])->names('categories');
    Route::get('/dataTable/categories', [CategoryController::class, 'dataTable'])->name('categories.dataTable');

    Route::resource('units', UnitController::class)->except(['create', 'edit'])->names('units');
    Route::get('/dataTable/units', [UnitController::class, 'dataTable'])->name('units.dataTable');

    Route::resource('sizes', SizeController::class)->except(['create', 'edit'])->names('sizes');
    Route::get('/dataTable/sizes', [SizeController::class, 'dataTable'])->name('sizes.dataTable');

    Route::resource('tax-rates', TaxRateController::class)->except(['create', 'edit'])->names('tax-rates');
    Route::get('/dataTable/tax-rates', [TaxRateController::class, 'dataTable'])->name('tax-rates.dataTable');

    // Product Requests
    Route::get('/product-requests', [ProductRequestController::class, 'index'])->name('product-requests.index');
    Route::get('/dataTable/product-requests', [ProductRequestController::class, 'dataTable'])->name('product-requests.dataTable');
    Route::post('/product-requests', [ProductRequestController::class, 'store'])->name('product-requests.store');
    Route::get('/product-requests/{id}', [ProductRequestController::class, 'show'])->name('product-requests.show');
    Route::post('/product-requests/{id}', [ProductRequestController::class, 'update'])->name('product-requests.update');
    Route::delete('/product-requests/{id}', [ProductRequestController::class, 'destroy'])->name('product-requests.destroy');
    Route::post('/product-requests/{id}/status', [ProductRequestController::class, 'updateStatus'])->name('product-requests.status');
});
