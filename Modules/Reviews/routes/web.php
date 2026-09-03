<?php

use Illuminate\Support\Facades\Route;
use Modules\Reviews\Http\Controllers\AuditLogController;
use Modules\Reviews\Http\Controllers\NotificationController;
use Modules\Reviews\Http\Controllers\ProductReviewController;
use Modules\Reviews\Http\Controllers\WebhookController;
use Modules\Reviews\Http\Controllers\WebhookDeliveryController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Product Reviews
    Route::resource('product-reviews', ProductReviewController::class)->except(['create', 'edit'])->names('product-reviews');
    Route::get('/dataTable/product-reviews', [ProductReviewController::class, 'dataTable'])->name('product-reviews.dataTable');
    Route::post('/product-reviews/{id}/approve', [ProductReviewController::class, 'approve'])->name('product-reviews.approve');

    // Notifications
    Route::resource('notifications', NotificationController::class)->except(['create', 'edit'])->names('notifications');
    Route::get('/dataTable/notifications', [NotificationController::class, 'dataTable'])->name('notifications.dataTable');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');

    // Navbar notification bell (polling endpoints)
    Route::get('/notifications/bell', [NotificationController::class, 'bell'])->name('notifications.bell');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // Audit Logs
    Route::resource('audit-logs', AuditLogController::class)->except(['create', 'edit'])->names('audit-logs');
    Route::get('/dataTable/audit-logs', [AuditLogController::class, 'dataTable'])->name('audit-logs.dataTable');

    // Webhooks
    Route::resource('webhooks', WebhookController::class)->except(['create', 'edit'])->names('webhooks');
    Route::get('/dataTable/webhooks', [WebhookController::class, 'dataTable'])->name('webhooks.dataTable');

    // Webhook Deliveries
    Route::resource('webhook-deliveries', WebhookDeliveryController::class)->except(['create', 'edit'])->names('webhook-deliveries');
    Route::get('/dataTable/webhook-deliveries', [WebhookDeliveryController::class, 'dataTable'])->name('webhook-deliveries.dataTable');
});
