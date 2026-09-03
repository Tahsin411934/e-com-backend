<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\DeliveryDriverController;
use Modules\Shipping\Http\Controllers\DeliveryZoneController;
use Modules\Shipping\Http\Controllers\ShipmentController;
use Modules\Shipping\Http\Controllers\ShipmentEventController;

Route::middleware(['convert.auth.cookie', 'auth:sanctum', 'role:Super Admin,Admin,Manager,Staff'])->prefix('v1')->group(function () {
    Route::apiResource('delivery-zones', DeliveryZoneController::class)->names('delivery-zones');
    Route::apiResource('delivery-drivers', DeliveryDriverController::class)->names('delivery-drivers');
    Route::apiResource('shipments', ShipmentController::class)->names('shipments');
    Route::apiResource('shipment-events', ShipmentEventController::class)->names('shipment-events');
});
