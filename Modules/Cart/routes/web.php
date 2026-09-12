<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\CampaignController;
use Modules\Cart\Http\Controllers\CartController;
use Modules\Cart\Http\Controllers\CouponController;
use Modules\Cart\Http\Controllers\WishlistController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Cart routes
    Route::resource('cart', CartController::class)->except(['create', 'edit'])->names('cart')->middleware('permission:carts.*');
    Route::get('/dataTable/carts', [CartController::class, 'dataTable'])->name('cart.dataTable')->middleware('permission:carts.view');

    // Coupons
    Route::resource('coupons', CouponController::class)->except(['create', 'edit'])->names('coupons')->middleware('permission:coupons.*');
    Route::get('/dataTable/coupons', [CouponController::class, 'dataTable'])->name('coupons.dataTable')->middleware('permission:coupons.view');

    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index')->middleware('permission:campaigns.view');
    Route::get('/campaigns/list', [CampaignController::class, 'list'])->name('campaigns.list')->middleware('permission:campaigns.view');
    Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store')->middleware('permission:campaigns.create');
    Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show')->middleware('permission:campaigns.view');
    Route::put('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update')->middleware('permission:campaigns.edit');
    Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy')->middleware('permission:campaigns.delete');
    Route::post('/campaigns/{campaign}/toggle-active', [CampaignController::class, 'toggleActive'])->name('campaigns.toggleActive')->middleware('permission:campaigns.edit');
    Route::get('/campaign-products/search', [CampaignController::class, 'searchProducts'])->name('campaigns.products.search')->middleware('permission:campaigns.view');
    Route::post('/campaigns/{campaign}/products', [CampaignController::class, 'addProduct'])->name('campaigns.products.add')->middleware('permission:campaigns.edit');
    Route::post('/campaigns/{campaign}/products/reorder', [CampaignController::class, 'reorderProducts'])->name('campaigns.products.reorder')->middleware('permission:campaigns.edit');
    Route::put('/campaigns/{campaign}/products/{campaignProduct}', [CampaignController::class, 'updateProduct'])->name('campaigns.products.update')->middleware('permission:campaigns.edit');
    Route::delete('/campaigns/{campaign}/products/{campaignProduct}', [CampaignController::class, 'removeProduct'])->name('campaigns.products.remove')->middleware('permission:campaigns.edit');

    // Wishlists
    Route::resource('wishlists', WishlistController::class)->except(['create', 'edit'])->names('wishlists')->middleware('permission:wishlists.*');
    Route::get('/dataTable/wishlists', [WishlistController::class, 'dataTable'])->name('wishlists.dataTable')->middleware('permission:wishlists.view');
});
