<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\DeliveryDriverController;
use Modules\Shipping\Http\Controllers\DeliveryZoneController;
use Modules\Shipping\Http\Controllers\ShipmentController;
use Modules\Shipping\Http\Controllers\ShipmentEventController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('delivery-zones', DeliveryZoneController::class)->except(['create', 'edit'])->names('delivery-zones')->middleware('permission:delivery-zones.*');
    Route::get('/dataTable/delivery-zones', [DeliveryZoneController::class, 'dataTable'])->name('delivery-zones.dataTable')->middleware('permission:delivery-zones.view');

    Route::resource('delivery-drivers', DeliveryDriverController::class)->except(['create', 'edit'])->names('delivery-drivers')->middleware('permission:delivery-drivers.*');
    Route::get('/dataTable/delivery-drivers', [DeliveryDriverController::class, 'dataTable'])->name('delivery-drivers.dataTable')->middleware('permission:delivery-drivers.view');

    Route::resource('shipments', ShipmentController::class)->except(['create', 'edit'])->names('shipments')->middleware('permission:shipments.*');
    Route::get('/dataTable/shipments', [ShipmentController::class, 'dataTable'])->name('shipments.dataTable')->middleware('permission:shipments.view');

    Route::resource('shipment-events', ShipmentEventController::class)->except(['create', 'edit'])->names('shipment-events')->middleware('permission:shipment-events.*');
    Route::get('/dataTable/shipment-events', [ShipmentEventController::class, 'dataTable'])->name('shipment-events.dataTable')->middleware('permission:shipment-events.view');
});
