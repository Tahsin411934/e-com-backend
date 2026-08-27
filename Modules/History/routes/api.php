<?php

use Illuminate\Support\Facades\Route;
use Modules\History\Http\Controllers\HistoryController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('histories', [HistoryController::class, 'index'])->name('history.index');
    Route::get('histories/{id}', [HistoryController::class, 'show'])->name('history.show');
    Route::post('histories/{id}/restore', [HistoryController::class, 'restore'])->name('history.restore');
});
