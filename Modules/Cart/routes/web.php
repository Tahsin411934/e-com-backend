<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\CouponController;
use Modules\Cart\Http\Controllers\CartController;
use Modules\Cart\Http\Controllers\WishlistController;
use Modules\Cart\Http\Controllers\CampaignController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Cart routes
    Route::resource('cart', CartController::class)->except(['create', 'edit'])->names('cart');
    Route::get('/dataTable/carts', [CartController::class, 'dataTable'])->name('cart.dataTable');

    // Coupons
    Route::resource('coupons', CouponController::class)->except(['create', 'edit'])->names('coupons');
    Route::get('/dataTable/coupons', [CouponController::class, 'dataTable'])->name('coupons.dataTable');

    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/list', [CampaignController::class, 'list'])->name('campaigns.list');
    Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
    Route::put('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
    Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');
    Route::post('/campaigns/{campaign}/toggle-active', [CampaignController::class, 'toggleActive'])->name('campaigns.toggleActive');
    Route::get('/campaign-products/search', [CampaignController::class, 'searchProducts'])->name('campaigns.products.search');
    Route::post('/campaigns/{campaign}/products', [CampaignController::class, 'addProduct'])->name('campaigns.products.add');
    Route::delete('/campaigns/{campaign}/products/{campaignProduct}', [CampaignController::class, 'removeProduct'])->name('campaigns.products.remove');

    // Wishlists
    Route::resource('wishlists', WishlistController::class)->except(['create', 'edit'])->names('wishlists');
    Route::get('/dataTable/wishlists', [WishlistController::class, 'dataTable'])->name('wishlists.dataTable');
});
