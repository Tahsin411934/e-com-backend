<?php

use Illuminate\Support\Facades\Route;
use Modules\Pos\Http\Controllers\PosController;

Route::middleware(['convert.auth.cookie', 'auth:sanctum', 'role:Super Admin,Admin,Manager,Staff'])->prefix('v1')->group(function () {
    Route::apiResource('pos', PosController::class)->names('pos');
});
