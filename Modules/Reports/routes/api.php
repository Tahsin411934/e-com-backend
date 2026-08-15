<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ReportApiController;

/*
|--------------------------------------------------------------------------
| Reports - API Routes
|--------------------------------------------------------------------------
|
| JSON endpoints powering the report pages and the CSV export buttons.
|
|   GET /api/reports/{category}/{method}           -> report payload
|   GET /api/reports/{category}/{method}/export    -> CSV download
|
| Supported filters (query string, shared): period, date_from, date_to,
| store_id, product_id, category_id, brand_id.
|
*/

Route::middleware(['auth:sanctum', 'convert.auth.cookie'])->prefix('v1/reports')->group(function () {
    Route::get('/{category}/{method}', [ReportApiController::class, 'report']);
    Route::get('/{category}/{method}/export', [ReportApiController::class, 'export']);
});