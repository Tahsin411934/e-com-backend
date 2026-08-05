<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\AuthController;

Route::post('/v1/register', [AuthController::class, 'register']);
Route::post('/v1/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/v1/me', [AuthController::class, 'me']);
    Route::post('/v1/logout', [AuthController::class, 'logout']);
    Route::post('/v1/logout-all', [AuthController::class, 'logoutAll']);
});
