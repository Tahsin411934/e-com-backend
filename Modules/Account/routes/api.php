<?php

use Illuminate\Support\Facades\Route;
use Modules\Account\Http\Controllers\AccountController;

Route::middleware(['auth:sanctum', 'role:Super Admin,Admin'])->prefix('v1')->group(function () {
    Route::apiResource('accounts', AccountController::class)->names('account');
});
