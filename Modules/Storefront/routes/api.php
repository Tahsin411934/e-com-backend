<?php

use Illuminate\Support\Facades\Route;
use Modules\Storefront\Http\Controllers\AnnouncementBarController;
use Modules\Storefront\Http\Controllers\BannerController;
use Modules\Storefront\Http\Controllers\BrandController;
use Modules\Storefront\Http\Controllers\CampaignController;
use Modules\Storefront\Http\Controllers\CategoryController;
use Modules\Storefront\Http\Controllers\HomeController;
use Modules\Storefront\Http\Controllers\NavbarController;
use Modules\Storefront\Http\Controllers\OrderController;
use Modules\Storefront\Http\Controllers\ProductController;
use Modules\Storefront\Http\Controllers\ProductRequestController;
use Modules\Storefront\Http\Controllers\ProductSearchController;
use Modules\Storefront\Http\Controllers\SettingsController;
use Modules\Storefront\Http\Controllers\SitemapController;
use Modules\Storefront\Http\Controllers\StoreResolveController;
use Modules\Storefront\Http\Controllers\SubnavbarController;

/*
|--------------------------------------------------------------------------
| Storefront (multi-tenant) API routes
|--------------------------------------------------------------------------
|
| Every route below is tenant-scoped. The `storefront.tenant` middleware
| resolves the store from the storefront host (X-Store-Host / Host header)
| and binds it as the CurrentStore for the request.
|
| The endpoints mirror the legacy /api/v1 frontend APIs, except every
| query is scoped exclusively to rows owned by the resolved store. The legacy
| APIs remain untouched.
|
*/

Route::middleware('storefront.tenant')->prefix('v1/storefront')->group(function () {
    // Navbar API - resolved per storefront
    Route::get('/navbar-items', [NavbarController::class, 'index'])->name('navbar-items.index');
    Route::get('/navbar-items/{id}', [NavbarController::class, 'show'])->name('navbar-items.show');
    Route::get('/navbar-items/{navbarItemId}/children', [NavbarController::class, 'children'])->name('navbar-items.children');

    // Banner API
    Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
    Route::get('/banners/{id}', [BannerController::class, 'show'])->name('banners.show');

    // Announcement Bar API
    Route::get('/announcement-bars', [AnnouncementBarController::class, 'index'])->name('announcement-bars.index');
    Route::get('/announcement-bars/{id}', [AnnouncementBarController::class, 'show'])->name('announcement-bars.show');

    // Settings API - store overrides win over global platform values
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // Brands / Categories / Products
    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/{slug}', [CampaignController::class, 'show'])->name('campaigns.show');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/categories/{slug}/products', [CategoryController::class, 'products'])->name('categories.products');
    Route::get('/products/search', [ProductSearchController::class, 'search'])->name('products.search');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

    // Home API - products grouped by category with dynamic CTA sections
    Route::get('/home/products-by-category', [HomeController::class, 'productsByCategory'])->name('home.products-by-category');

    // Subnavbar Products API
    Route::get('/subnavbar/{slug}/products', [SubnavbarController::class, 'products'])->name('subnavbar.products');

    // Product Request API - submit a request from this storefront
    Route::post('/product-requests', [ProductRequestController::class, 'store'])->name('product-requests.store');

    // Sitemap API - chunked product feed for this storefront's sitemap
    Route::get('/sitemap/products-count', [SitemapController::class, 'productCount'])->name('sitemap.products-count');
    Route::get('/sitemap/products', [SitemapController::class, 'products'])->name('sitemap.products');
});

// Customer endpoints - authentication required, tenant still resolved first
Route::middleware(['storefront.tenant', 'auth:sanctum'])->prefix('v1/storefront')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Tenant discovery - intentionally OUTSIDE the storefront.tenant group so an
// unregistered host gets a structured answer ({registered: false, reason})
// instead of the middleware's hard 404. Consumed by the frontend proxy to
// route unregistered subdomains to a friendly "no store here" page.
Route::get('v1/storefront/resolve', [StoreResolveController::class, 'show'])->name('resolve.show');
