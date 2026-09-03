<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\CheckoutController;
use Modules\Order\Http\Controllers\DeliveryController;
use Modules\Order\Http\Controllers\OrderController;
use Modules\Order\Http\Controllers\PaymentController;
use Modules\Order\Http\Controllers\RefundController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Checkout - Convert cart to order
    Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');

    // Deliveries
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/{id}', [DeliveryController::class, 'show'])->name('deliveries.show');
    Route::post('/deliveries/{id}/assign', [DeliveryController::class, 'assign'])->name('deliveries.assign');
    Route::put('/deliveries/{id}/status', [DeliveryController::class, 'updateStatus'])->name('deliveries.updateStatus');
    Route::get('/my-deliveries', [DeliveryController::class, 'myDeliveries'])->name('deliveries.myDeliveries');

    // Orders
    Route::resource('orders', OrderController::class)->except(['create', 'edit'])->names('orders');
    Route::get('/dataTable/orders', [OrderController::class, 'dataTable'])->name('orders.dataTable');

    // Payments
    Route::resource('payments', PaymentController::class)->except(['create', 'edit'])->names('payments');
    Route::get('/dataTable/payments', [PaymentController::class, 'dataTable'])->name('payments.dataTable');

    // Refunds
    Route::resource('refunds', RefundController::class)->except(['create', 'edit'])->names('refunds');
    Route::get('/dataTable/refunds', [RefundController::class, 'dataTable'])->name('refunds.dataTable');
});
