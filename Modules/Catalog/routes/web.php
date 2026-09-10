<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Http\Controllers\BarcodePrintController;
use Modules\Catalog\Http\Controllers\BrandController;
use Modules\Catalog\Http\Controllers\CategoryController;
use Modules\Catalog\Http\Controllers\ProductController;
use Modules\Catalog\Http\Controllers\ProductRequestController;
use Modules\Catalog\Http\Controllers\SizeController;
use Modules\Catalog\Http\Controllers\TaxRateController;
use Modules\Catalog\Http\Controllers\UnitController;

/*
|--------------------------------------------------------------------------
| Catalog - tenant accessible (platform staff + SaaS store owners)
|--------------------------------------------------------------------------
| Products, brands and categories are the store owner's working area.
| Data is still global until per-store scoping lands (next phase).
*/
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/barcode-print', [BarcodePrintController::class, 'index'])->name('barcode-print.index')->middleware('permission:barcode-print.view');
    Route::get('/barcode-print/search', [BarcodePrintController::class, 'search'])->name('barcode-print.search')->middleware('permission:barcode-print.view');
    Route::get('/barcode-print/autocomplete', [BarcodePrintController::class, 'autocomplete'])->name('barcode-print.autocomplete')->middleware('permission:barcode-print.view');
    Route::get('/barcode-print/variants/{product}', [BarcodePrintController::class, 'variants'])->name('barcode-print.variants')->middleware('permission:barcode-print.view');
    Route::post('/barcode-print/print', [BarcodePrintController::class, 'print'])->name('barcode-print.print')->middleware('permission:barcode-print.view');

    Route::post('/products/reorder', [ProductController::class, 'reorder'])->name('products.reorder')->middleware('permission:products.edit');
    Route::post('/products/{id}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate')->middleware('permission:products.create');
    Route::get('products', [ProductController::class, 'index'])->name('products.index')->middleware('permission:products.view');
    Route::get('products/create', [ProductController::class, 'create'])->name('products.create')->middleware('permission:products.create');
    Route::post('products', [ProductController::class, 'store'])->name('products.store')->middleware('permission:products.create');
    Route::get('products/{id}', [ProductController::class, 'show'])->name('products.show')->middleware('permission:products.view');
    Route::get('products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit')->middleware('permission:products.edit');
    Route::put('products/{id}', [ProductController::class, 'update'])->name('products.update')->middleware('permission:products.edit');
    Route::patch('products/{id}', [ProductController::class, 'update'])->middleware('permission:products.edit');
    Route::delete('products/{id}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('permission:products.delete');
    Route::get('/dataTable/products', [ProductController::class, 'dataTable'])->name('products.dataTable')->middleware('permission:products.view');

    Route::get('brands', [BrandController::class, 'index'])->name('brands.index')->middleware('permission:brands.view');
    Route::get('/dataTable/brands', [BrandController::class, 'dataTable'])->name('brands.dataTable')->middleware('permission:brands.view');
    Route::get('brands/{id}', [BrandController::class, 'show'])->name('brands.show')->middleware('permission:brands.view');
    Route::post('brands', [BrandController::class, 'store'])->name('brands.store')->middleware('permission:brands.create');
    Route::put('brands/{id}', [BrandController::class, 'update'])->name('brands.update')->middleware('permission:brands.edit');
    Route::patch('brands/{id}', [BrandController::class, 'update'])->middleware('permission:brands.edit');
    Route::delete('brands/{id}', [BrandController::class, 'destroy'])->name('brands.destroy')->middleware('permission:brands.delete');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index')->middleware('permission:categories.view');
    Route::get('/dataTable/categories', [CategoryController::class, 'dataTable'])->name('categories.dataTable')->middleware('permission:categories.view');
    Route::get('categories/{id}', [CategoryController::class, 'show'])->name('categories.show')->middleware('permission:categories.view');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store')->middleware('permission:categories.create');
    Route::put('categories/{id}', [CategoryController::class, 'update'])->name('categories.update')->middleware('permission:categories.edit');
    Route::patch('categories/{id}', [CategoryController::class, 'update'])->middleware('permission:categories.edit');
    Route::delete('categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('permission:categories.delete');
});

/*
|--------------------------------------------------------------------------
| Catalog - platform only (reference data & moderation)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('units', UnitController::class)->except(['create', 'edit'])->names('units')->middleware('permission:units.*');
    Route::get('/dataTable/units', [UnitController::class, 'dataTable'])->name('units.dataTable')->middleware('permission:units.view');

    Route::resource('sizes', SizeController::class)->except(['create', 'edit'])->names('sizes')->middleware('permission:sizes.*');
    Route::get('/dataTable/sizes', [SizeController::class, 'dataTable'])->name('sizes.dataTable')->middleware('permission:sizes.view');

    Route::resource('tax-rates', TaxRateController::class)->except(['create', 'edit'])->names('tax-rates')->middleware('permission:tax-rates.*');
    Route::get('/dataTable/tax-rates', [TaxRateController::class, 'dataTable'])->name('tax-rates.dataTable')->middleware('permission:tax-rates.view');

    // Product Requests
    Route::get('/product-requests', [ProductRequestController::class, 'index'])->name('product-requests.index')->middleware('permission:product-requests.view');
    Route::get('/dataTable/product-requests', [ProductRequestController::class, 'dataTable'])->name('product-requests.dataTable')->middleware('permission:product-requests.view');
    Route::post('/product-requests', [ProductRequestController::class, 'store'])->name('product-requests.store')->middleware('permission:product-requests.create');
    Route::get('/product-requests/{id}', [ProductRequestController::class, 'show'])->name('product-requests.show')->middleware('permission:product-requests.view');
    Route::post('/product-requests/{id}', [ProductRequestController::class, 'update'])->name('product-requests.update')->middleware('permission:product-requests.edit');
    Route::delete('/product-requests/{id}', [ProductRequestController::class, 'destroy'])->name('product-requests.destroy')->middleware('permission:product-requests.delete');
    Route::post('/product-requests/{id}/status', [ProductRequestController::class, 'updateStatus'])->name('product-requests.status')->middleware('permission:product-requests.edit');
});
