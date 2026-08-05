<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\FrontendController;
use Modules\Frontend\Http\Controllers\NavbarApiController;
use Modules\Frontend\Http\Controllers\BannerApiController;
use Modules\Frontend\Http\Controllers\BrandApiController;
use Modules\Frontend\Http\Controllers\CategoryApiController;
use Modules\Frontend\Http\Controllers\ProductSearchApiController;
use Modules\Frontend\Http\Controllers\HomeApiController;
use Modules\Frontend\Http\Controllers\ProductApiController;
use Modules\Frontend\Http\Controllers\SubnavbarApiController;
use Modules\Frontend\Http\Controllers\ProductRequestApiController;
use Modules\Frontend\Http\Controllers\AnnouncementBarApiController;
use Modules\Frontend\Http\Controllers\SettingsApiController;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
|
| Navbar, subnavbar, and banner data is public-facing for the frontend store.
|
*/

Route::prefix('v1')->group(function () {
    // Navbar API - publicly accessible (for store frontend)
    Route::get('/navbar-items', [NavbarApiController::class, 'index'])->name('api.navbar-items.index');
    Route::get('/navbar-items/{id}', [NavbarApiController::class, 'show'])->name('api.navbar-items.show');
    Route::get('/navbar-items/{navbarItemId}/children', [NavbarApiController::class, 'children'])->name('api.navbar-items.children');

    // Banner API - publicly accessible (for store frontend)
    Route::get('/banners', [BannerApiController::class, 'index'])->name('api.banners.index');
    Route::get('/banners/{id}', [BannerApiController::class, 'show'])->name('api.banners.show');

    // Brands API - publicly accessible (for store frontend filter dropdowns)
    Route::get('/brands', [BrandApiController::class, 'index'])->name('api.brands.index');

    // Category API - publicly accessible (for store frontend)
    // Returns all categories (parent + child) in one flat list.
    // Frontend can build hierarchy using the parent_id field.
    Route::get('/categories', [CategoryApiController::class, 'index'])->name('api.categories.index');

    // Single Category by Slug API - returns category info without products
    Route::get('/categories/{slug}', [CategoryApiController::class, 'show'])->name('api.categories.show');

    // Category Products API - single category with paginated products
    Route::get('/categories/{slug}/products', [CategoryApiController::class, 'products'])->name('api.categories.products');

    // Product Search API - fuzzy matching with FULLTEXT + Levenshtein
    // MUST be defined before /products/{slug} to avoid route conflict
    Route::get('/products/search', [ProductSearchApiController::class, 'search'])->name('api.products.search');

    // Product Detail API - single product with full details, reviews, variants, related products
    Route::get('/products/{slug}', [ProductApiController::class, 'show'])->name('api.products.show');

    // Home API - products grouped by category with dynamic CTA sections
    Route::get('/home/products-by-category', [HomeApiController::class, 'productsByCategory'])->name('api.home.products-by-category');

    // Subnavbar Products API - get products by subnavbar slug
    Route::get('/subnavbar/{slug}/products', [SubnavbarApiController::class, 'products'])->name('api.subnavbar.products');

    // Announcement Bar API - top header announcement
    Route::get('/announcement-bars', [AnnouncementBarApiController::class, 'index'])->name('api.announcement-bars.index');
    Route::get('/announcement-bars/{id}', [AnnouncementBarApiController::class, 'show'])->name('api.announcement-bars.show');

    // Settings API - site configuration (logo, name, social, contact, SEO)
    Route::get('/settings', [SettingsApiController::class, 'index'])->name('api.settings.index');

    // Product Request API - submit a product request from frontend
    Route::post('/product-requests', [ProductRequestApiController::class, 'store'])->name('api.product-requests.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
|
| Standard module API routes (require authentication).
|
*/

Route::middleware(['convert.auth.cookie', 'auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('frontends', FrontendController::class)->names('frontend');
});