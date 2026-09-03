<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;

Route::middleware(['convert.auth.cookie', 'auth:sanctum', 'role:Super Admin,Admin,Manager,Staff'])->prefix('v1')->group(function () {
    Route::apiResource('inventories', InventoryController::class)->names('inventory');
});
