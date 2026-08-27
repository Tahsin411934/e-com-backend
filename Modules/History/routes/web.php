<?php

use Illuminate\Support\Facades\Route;
use Modules\History\Http\Controllers\HistoryController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('histories', [HistoryController::class, 'page'])->name('history.page');

    // Session-authenticated JSON endpoints used by the /histories admin page
    // (the browser has no Bearer token, matching the dataTable convention
    // used by the other admin modules).
    Route::get('histories/data', [HistoryController::class, 'index'])->name('history.data');
    Route::post('histories/{id}/restore', [HistoryController::class, 'restore'])->name('history.restore');
});
