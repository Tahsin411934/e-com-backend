<?php

use Illuminate\Support\Facades\Route;
use Modules\Reviews\Http\Controllers\AuditLogController;
use Modules\Reviews\Http\Controllers\NotificationController;
use Modules\Reviews\Http\Controllers\ProductReviewController;
use Modules\Reviews\Http\Controllers\WebhookController;
use Modules\Reviews\Http\Controllers\WebhookDeliveryController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Product Reviews
    Route::resource('product-reviews', ProductReviewController::class)->except(['create', 'edit'])->names('product-reviews')->middleware('permission:reviews.*');
    Route::get('/dataTable/product-reviews', [ProductReviewController::class, 'dataTable'])->name('product-reviews.dataTable')->middleware('permission:reviews.view');
    Route::post('/product-reviews/{id}/approve', [ProductReviewController::class, 'approve'])->name('product-reviews.approve')->middleware('permission:reviews.moderate');

    // Notifications
    Route::resource('notifications', NotificationController::class)->except(['create', 'edit'])->names('notifications')->middleware('permission:notifications.*');
    Route::get('/dataTable/notifications', [NotificationController::class, 'dataTable'])->name('notifications.dataTable')->middleware('permission:notifications.view');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read')->middleware('permission:notifications.edit');

    // Navbar notification bell (polling endpoints) — accessible to any admin user
    Route::get('/notifications/bell', [NotificationController::class, 'bell'])->name('notifications.bell');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // Audit Logs
    Route::resource('audit-logs', AuditLogController::class)->except(['create', 'edit'])->names('audit-logs')->middleware('permission:audit-logs.*');
    Route::get('/dataTable/audit-logs', [AuditLogController::class, 'dataTable'])->name('audit-logs.dataTable')->middleware('permission:audit-logs.view');

    // Webhooks
    Route::resource('webhooks', WebhookController::class)->except(['create', 'edit'])->names('webhooks')->middleware('permission:webhooks.*');
    Route::get('/dataTable/webhooks', [WebhookController::class, 'dataTable'])->name('webhooks.dataTable')->middleware('permission:webhooks.view');

    // Webhook Deliveries
    Route::resource('webhook-deliveries', WebhookDeliveryController::class)->except(['create', 'edit'])->names('webhook-deliveries')->middleware('permission:webhook-deliveries.*');
    Route::get('/dataTable/webhook-deliveries', [WebhookDeliveryController::class, 'dataTable'])->name('webhook-deliveries.dataTable')->middleware('permission:webhook-deliveries.view');
});
