<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\NavbarController;

use Modules\Frontend\Http\Controllers\BannerController;
use Modules\Frontend\Http\Controllers\HomepageCtaController;
use Modules\Frontend\Http\Controllers\AnnouncementBarController;
use Modules\Frontend\Http\Controllers\SiteSettingController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Navbar Items page
    Route::get('/nav-items', [NavbarController::class, 'index'])->name('frontend.nav-items.index');

    // Subnavbar Items page (optionally filtered by navbar_item_id)
    Route::get('/sub-nav-items', [NavbarController::class, 'subnavbarIndex'])->name('frontend.nav-items.subnavbar.index');

    // Navbar Item routes
    Route::get('/dataTable/navbar-items', [NavbarController::class, 'navbarDataTable'])->name('frontend.nav-items.navbar.dataTable');
    Route::post('/navbar-items', [NavbarController::class, 'storeNavbarItem'])->name('frontend.nav-items.navbar.store');
    Route::get('/navbar-items/{id}', [NavbarController::class, 'showNavbarItem'])->name('frontend.nav-items.navbar.show');
    Route::put('/navbar-items/{id}', [NavbarController::class, 'updateNavbarItem'])->name('frontend.nav-items.navbar.update');
    Route::delete('/navbar-items/{id}', [NavbarController::class, 'destroyNavbarItem'])->name('frontend.nav-items.navbar.destroy');
    Route::get('/navbar-items-list', [NavbarController::class, 'getNavbarItemsList'])->name('frontend.nav-items.navbar.list');

    // Subnavbar Item routes
    Route::get('/dataTable/subnavbar-items', [NavbarController::class, 'subnavbarDataTable'])->name('frontend.nav-items.subnavbar.dataTable');
    Route::post('/subnavbar-items', [NavbarController::class, 'storeSubnavbarItem'])->name('frontend.nav-items.subnavbar.store');
    Route::get('/subnavbar-items/{id}', [NavbarController::class, 'showSubnavbarItem'])->name('frontend.nav-items.subnavbar.show');
    Route::put('/subnavbar-items/{id}', [NavbarController::class, 'updateSubnavbarItem'])->name('frontend.nav-items.subnavbar.update');
    Route::delete('/subnavbar-items/{id}', [NavbarController::class, 'destroySubnavbarItem'])->name('frontend.nav-items.subnavbar.destroy');

    // Banner routes
    Route::get('/banners', [BannerController::class, 'index'])->name('frontend.banners.index');
    Route::get('/dataTable/banners', [BannerController::class, 'dataTable'])->name('frontend.banners.dataTable');
    Route::post('/banners', [BannerController::class, 'store'])->name('frontend.banners.store');
    Route::get('/banners/{id}', [BannerController::class, 'show'])->name('frontend.banners.show');
    Route::match(['post', 'put'], '/banners/{id}', [BannerController::class, 'update'])->name('frontend.banners.update');
    Route::delete('/banners/{id}', [BannerController::class, 'destroy'])->name('frontend.banners.destroy');

    // Homepage CTA routes
    Route::get('/homepage-ctas', [HomepageCtaController::class, 'index'])->name('frontend.ctas.index');
    Route::get('/dataTable/homepage-ctas', [HomepageCtaController::class, 'dataTable'])->name('frontend.ctas.dataTable');
    Route::post('/homepage-ctas', [HomepageCtaController::class, 'store'])->name('frontend.ctas.store');
    Route::get('/homepage-ctas/{id}', [HomepageCtaController::class, 'show'])->name('frontend.ctas.show');
    Route::match(['post', 'put'], '/homepage-ctas/{id}', [HomepageCtaController::class, 'update'])->name('frontend.ctas.update');
    Route::delete('/homepage-ctas/{id}', [HomepageCtaController::class, 'destroy'])->name('frontend.ctas.destroy');

    // Announcement Bar routes
    Route::get('/announcement-bars', [AnnouncementBarController::class, 'index'])->name('frontend.announcement-bars.index');
    Route::get('/dataTable/announcement-bars', [AnnouncementBarController::class, 'dataTable'])->name('frontend.announcement-bars.dataTable');
    Route::post('/announcement-bars', [AnnouncementBarController::class, 'store'])->name('frontend.announcement-bars.store');
    Route::get('/announcement-bars/{id}', [AnnouncementBarController::class, 'show'])->name('frontend.announcement-bars.show');
    Route::match(['post', 'put'], '/announcement-bars/{id}', [AnnouncementBarController::class, 'update'])->name('frontend.announcement-bars.update');
    Route::delete('/announcement-bars/{id}', [AnnouncementBarController::class, 'destroy'])->name('frontend.announcement-bars.destroy');

    // Site Settings routes
    Route::get('/site-settings', [SiteSettingController::class, 'index'])->name('frontend.site-settings.index');
    Route::put('/site-settings', [SiteSettingController::class, 'update'])->name('frontend.site-settings.update');
    Route::get('/site-settings/seed', [SiteSettingController::class, 'seed'])->name('frontend.site-settings.seed');

    // Marketing - Google Tag Manager (GTM) settings
    Route::get('/marketing/gtm', [SiteSettingController::class, 'marketingGtm'])->name('frontend.marketing.gtm.index');
    Route::put('/marketing/gtm', [SiteSettingController::class, 'update'])->name('frontend.marketing.gtm.update');
});
