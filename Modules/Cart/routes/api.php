<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\CartController;
use Modules\Cart\Http\Controllers\WishlistController;
use Modules\Cart\Http\Controllers\CampaignApiController;

Route::prefix('v1')->group(function () {
    Route::get('campaigns', [CampaignApiController::class, 'index']);
    Route::get('campaigns/{slug}', [CampaignApiController::class, 'show']);
});

Route::middleware(['convert.auth.cookie', 'auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('carts', CartController::class)->names('cart');
    
    // Cart item operations
    Route::post('carts/add-item', [CartController::class, 'addItem'])->name('cart.addItem');
    Route::put('carts/items/{itemId}', [CartController::class, 'updateItem'])->name('cart.updateItem');
    Route::delete('carts/items/{itemId}', [CartController::class, 'removeItem'])->name('cart.removeItem');
    
    // Get current user's active cart
    Route::get('carts/my-cart', [CartController::class, 'myCart'])->name('cart.myCart');

    // Sync cart from frontend to backend
    Route::post('carts/sync', [CartController::class, 'syncCart'])->name('cart.sync');

    // Checkout - Convert cart to order
    Route::post('checkout', [\Modules\Order\Http\Controllers\CheckoutController::class, 'checkout'])->name('cart.checkout');

    // Wishlist API routes
    Route::get('wishlists', [WishlistController::class, 'apiIndex'])->name('wishlists.index');
    Route::post('wishlists/toggle', [WishlistController::class, 'apiToggle'])->name('wishlists.toggle');
    Route::delete('wishlists/{productId}', [WishlistController::class, 'apiRemove'])->name('wishlists.remove');
});
