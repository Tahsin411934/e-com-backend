<?php

use Illuminate\Support\Facades\Route;
use Modules\History\Http\Controllers\HistoryController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('histories', [HistoryController::class, 'page'])->name('history.page');

    // DataTables endpoint for the /histories admin page (same convention as
    // /dataTable/users, /dataTable/carts, etc.).
    Route::get('/dataTable/histories', [HistoryController::class, 'dataTable'])->name('history.dataTable');

    // Session-authenticated restore endpoint used by the admin page
    // (the browser has no Bearer token, matching the dataTable convention
    // used by the other admin modules).
    Route::post('histories/{id}/restore', [HistoryController::class, 'restore'])->name('history.restore');
});
