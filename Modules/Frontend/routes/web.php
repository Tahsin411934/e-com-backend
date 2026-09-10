<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\AnnouncementBarController;
use Modules\Frontend\Http\Controllers\BannerController;
use Modules\Frontend\Http\Controllers\HomepageCtaController;
use Modules\Frontend\Http\Controllers\NavbarController;
use Modules\Frontend\Http\Controllers\SiteSettingController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Navbar Items page
    Route::get('/nav-items', [NavbarController::class, 'index'])->name('frontend.nav-items.index')->middleware('permission:frontend.navbar.view');

    // Subnavbar Items page (optionally filtered by navbar_item_id)
    Route::get('/sub-nav-items', [NavbarController::class, 'subnavbarIndex'])->name('frontend.nav-items.subnavbar.index')->middleware('permission:frontend.navbar.view');

    // Navbar Item routes
    Route::get('/dataTable/navbar-items', [NavbarController::class, 'navbarDataTable'])->name('frontend.nav-items.navbar.dataTable')->middleware('permission:frontend.navbar.view');
    Route::post('/navbar-items', [NavbarController::class, 'storeNavbarItem'])->name('frontend.nav-items.navbar.store')->middleware('permission:frontend.navbar.create');
    Route::get('/navbar-items/{id}', [NavbarController::class, 'showNavbarItem'])->name('frontend.nav-items.navbar.show')->middleware('permission:frontend.navbar.view');
    Route::put('/navbar-items/{id}', [NavbarController::class, 'updateNavbarItem'])->name('frontend.nav-items.navbar.update')->middleware('permission:frontend.navbar.edit');
    Route::delete('/navbar-items/{id}', [NavbarController::class, 'destroyNavbarItem'])->name('frontend.nav-items.navbar.destroy')->middleware('permission:frontend.navbar.delete');
    Route::get('/navbar-items-list', [NavbarController::class, 'getNavbarItemsList'])->name('frontend.nav-items.navbar.list')->middleware('permission:frontend.navbar.view');

    // Subnavbar Item routes
    Route::get('/dataTable/subnavbar-items', [NavbarController::class, 'subnavbarDataTable'])->name('frontend.nav-items.subnavbar.dataTable')->middleware('permission:frontend.navbar.view');
    Route::post('/subnavbar-items', [NavbarController::class, 'storeSubnavbarItem'])->name('frontend.nav-items.subnavbar.store')->middleware('permission:frontend.navbar.create');
    Route::get('/subnavbar-items/{id}', [NavbarController::class, 'showSubnavbarItem'])->name('frontend.nav-items.subnavbar.show')->middleware('permission:frontend.navbar.view');
    Route::put('/subnavbar-items/{id}', [NavbarController::class, 'updateSubnavbarItem'])->name('frontend.nav-items.subnavbar.update')->middleware('permission:frontend.navbar.edit');
    Route::delete('/subnavbar-items/{id}', [NavbarController::class, 'destroySubnavbarItem'])->name('frontend.nav-items.subnavbar.destroy')->middleware('permission:frontend.navbar.delete');

    // Banner routes
    Route::get('/banners', [BannerController::class, 'index'])->name('frontend.banners.index')->middleware('permission:frontend.banners.view');
    Route::get('/dataTable/banners', [BannerController::class, 'dataTable'])->name('frontend.banners.dataTable')->middleware('permission:frontend.banners.view');
    Route::post('/banners', [BannerController::class, 'store'])->name('frontend.banners.store')->middleware('permission:frontend.banners.create');
    Route::get('/banners/{id}', [BannerController::class, 'show'])->name('frontend.banners.show')->middleware('permission:frontend.banners.view');
    Route::match(['post', 'put'], '/banners/{id}', [BannerController::class, 'update'])->name('frontend.banners.update')->middleware('permission:frontend.banners.edit');
    Route::delete('/banners/{id}', [BannerController::class, 'destroy'])->name('frontend.banners.destroy')->middleware('permission:frontend.banners.delete');

    // Homepage CTA routes
    Route::get('/homepage-ctas', [HomepageCtaController::class, 'index'])->name('frontend.ctas.index')->middleware('permission:frontend.ctas.view');
    Route::get('/dataTable/homepage-ctas', [HomepageCtaController::class, 'dataTable'])->name('frontend.ctas.dataTable')->middleware('permission:frontend.ctas.view');
    Route::post('/homepage-ctas', [HomepageCtaController::class, 'store'])->name('frontend.ctas.store')->middleware('permission:frontend.ctas.create');
    Route::get('/homepage-ctas/{id}', [HomepageCtaController::class, 'show'])->name('frontend.ctas.show')->middleware('permission:frontend.ctas.view');
    Route::match(['post', 'put'], '/homepage-ctas/{id}', [HomepageCtaController::class, 'update'])->name('frontend.ctas.update')->middleware('permission:frontend.ctas.edit');
    Route::delete('/homepage-ctas/{id}', [HomepageCtaController::class, 'destroy'])->name('frontend.ctas.destroy')->middleware('permission:frontend.ctas.delete');

    // Announcement Bar routes
    Route::get('/announcement-bars', [AnnouncementBarController::class, 'index'])->name('frontend.announcement-bars.index')->middleware('permission:frontend.announcements.view');
    Route::get('/dataTable/announcement-bars', [AnnouncementBarController::class, 'dataTable'])->name('frontend.announcement-bars.dataTable')->middleware('permission:frontend.announcements.view');
    Route::post('/announcement-bars', [AnnouncementBarController::class, 'store'])->name('frontend.announcement-bars.store')->middleware('permission:frontend.announcements.create');
    Route::get('/announcement-bars/{id}', [AnnouncementBarController::class, 'show'])->name('frontend.announcement-bars.show')->middleware('permission:frontend.announcements.view');
    Route::match(['post', 'put'], '/announcement-bars/{id}', [AnnouncementBarController::class, 'update'])->name('frontend.announcement-bars.update')->middleware('permission:frontend.announcements.edit');
    Route::delete('/announcement-bars/{id}', [AnnouncementBarController::class, 'destroy'])->name('frontend.announcement-bars.destroy')->middleware('permission:frontend.announcements.delete');

    // Site Settings routes
    Route::get('/site-settings', [SiteSettingController::class, 'index'])->name('frontend.site-settings.index')->middleware('permission:frontend.settings.view');
    Route::put('/site-settings', [SiteSettingController::class, 'update'])->name('frontend.site-settings.update')->middleware('permission:frontend.settings.edit');
    Route::get('/site-settings/seed', [SiteSettingController::class, 'seed'])->name('frontend.site-settings.seed')->middleware('permission:frontend.settings.edit');

    // Marketing - Google Tag Manager (GTM) settings
    Route::get('/marketing/gtm', [SiteSettingController::class, 'marketingGtm'])->name('frontend.marketing.gtm.index')->middleware('permission:marketing.gtm.view');
    Route::put('/marketing/gtm', [SiteSettingController::class, 'update'])->name('frontend.marketing.gtm.update')->middleware('permission:marketing.gtm.edit');
});
