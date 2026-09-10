<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Reports - Web Routes
|--------------------------------------------------------------------------
|
| Server-rendered pages for each report category. Each page loads its data
| (KPIs, charts and tables) from the JSON API below and can export to CSV.
|
*/

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/reports', [ReportController::class, 'dashboard'])->name('reports.dashboard')->middleware('permission:reports.dashboard');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales')->middleware('permission:reports.sales');
    Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products')->middleware('permission:reports.products');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory')->middleware('permission:reports.inventory');
    Route::get('/reports/orders', [ReportController::class, 'orders'])->name('reports.orders')->middleware('permission:reports.orders');
    Route::get('/reports/shipping', [ReportController::class, 'shipping'])->name('reports.shipping')->middleware('permission:reports.shipping');
    Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers')->middleware('permission:reports.customers');
    Route::get('/reports/campaigns', [ReportController::class, 'campaigns'])->name('reports.campaigns')->middleware('permission:reports.campaigns');
    Route::get('/reports/finance', [ReportController::class, 'finance'])->name('reports.finance')->middleware('permission:reports.finance');
    Route::get('/reports/refunds', [ReportController::class, 'refunds'])->name('reports.refunds')->middleware('permission:reports.refunds');
    Route::get('/reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases')->middleware('permission:reports.purchases');
    Route::get('/reports/staff', [ReportController::class, 'staff'])->name('reports.staff')->middleware('permission:reports.staff');
});
