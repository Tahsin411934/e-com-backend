<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\CheckoutController;
use Modules\Order\Http\Controllers\DeliveryController;
use Modules\Order\Http\Controllers\OrderController;
use Modules\Order\Http\Controllers\PaymentController;
use Modules\Order\Http\Controllers\RefundController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Checkout - Convert cart to order
    Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout')->middleware('permission:orders.create');

    // Deliveries
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index')->middleware('permission:deliveries.view');
    Route::get('/deliveries/{id}', [DeliveryController::class, 'show'])->name('deliveries.show')->middleware('permission:deliveries.view');
    Route::post('/deliveries/{id}/assign', [DeliveryController::class, 'assign'])->name('deliveries.assign')->middleware('permission:deliveries.edit');
    Route::put('/deliveries/{id}/status', [DeliveryController::class, 'updateStatus'])->name('deliveries.updateStatus')->middleware('permission:deliveries.edit');
    Route::get('/my-deliveries', [DeliveryController::class, 'myDeliveries'])->name('deliveries.myDeliveries')->middleware('permission:deliveries.view');

    // Orders
    Route::resource('orders', OrderController::class)->except(['create', 'edit'])->names('orders')->middleware('permission:orders.*');
    Route::get('/dataTable/orders', [OrderController::class, 'dataTable'])->name('orders.dataTable')->middleware('permission:orders.view');
    Route::get('/orders/{order}/details', [OrderController::class, 'details'])->name('orders.details')->middleware('permission:orders.details');

    // Payments
    Route::resource('payments', PaymentController::class)->except(['create', 'edit'])->names('payments')->middleware('permission:payments.*');
    Route::get('/dataTable/payments', [PaymentController::class, 'dataTable'])->name('payments.dataTable')->middleware('permission:payments.view');

    // Refunds
    Route::resource('refunds', RefundController::class)->except(['create', 'edit'])->names('refunds')->middleware('permission:refunds.*');
    Route::get('/dataTable/refunds', [RefundController::class, 'dataTable'])->name('refunds.dataTable')->middleware('permission:refunds.view');
});
