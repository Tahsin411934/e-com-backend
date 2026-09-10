<?php

use Illuminate\Support\Facades\Route;
use Modules\Account\Http\Controllers\AccountAccountController;
use Modules\Account\Http\Controllers\AccountCategoryController;
use Modules\Account\Http\Controllers\AccountController;
use Modules\Account\Http\Controllers\AccountExpenseController;
use Modules\Account\Http\Controllers\AccountInvestmentController;
use Modules\Account\Http\Controllers\AccountReportController;
use Modules\Account\Http\Controllers\AccountTransferController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('account', [AccountController::class, 'index'])->name('account.dashboard')->middleware('permission:account-reports.view');

    Route::resource('account-accounts', AccountAccountController::class)->except(['create', 'edit'])->names('account-accounts')->middleware('permission:accounts.*');
    Route::get('/dataTable/account-accounts', [AccountAccountController::class, 'dataTable'])->name('account-accounts.dataTable')->middleware('permission:accounts.view');

    Route::resource('account-categories', AccountCategoryController::class)->except(['create', 'edit'])->names('account-categories')->middleware('permission:account-categories.*');
    Route::get('/dataTable/account-categories', [AccountCategoryController::class, 'dataTable'])->name('account-categories.dataTable')->middleware('permission:account-categories.view');

    Route::resource('account-expenses', AccountExpenseController::class)->except(['create', 'edit'])->names('account-expenses')->middleware('permission:account-expenses.*');
    Route::get('/dataTable/account-expenses', [AccountExpenseController::class, 'dataTable'])->name('account-expenses.dataTable')->middleware('permission:account-expenses.view');

    Route::resource('account-investments', AccountInvestmentController::class)->except(['create', 'edit'])->names('account-investments')->middleware('permission:account-investments.*');
    Route::get('/dataTable/account-investments', [AccountInvestmentController::class, 'dataTable'])->name('account-investments.dataTable')->middleware('permission:account-investments.view');

    Route::resource('account-transfers', AccountTransferController::class)->only(['index', 'store'])->names('account-transfers')->middleware('permission:account-transfers.*');
    Route::get('/dataTable/account-transfers', [AccountTransferController::class, 'dataTable'])->name('account-transfers.dataTable')->middleware('permission:account-transfers.view');

    Route::get('account-transactions', [AccountReportController::class, 'transactions'])->name('account-transactions.index')->middleware('permission:account-reports.view');
    Route::get('/dataTable/account-transactions', [AccountReportController::class, 'transactionsDataTable'])->name('account-transactions.dataTable')->middleware('permission:account-reports.view');

    Route::get('account-product-profits', [AccountReportController::class, 'productProfits'])->name('account-product-profits.index')->middleware('permission:account-reports.view');
    Route::get('/dataTable/account-product-profits', [AccountReportController::class, 'productProfitsDataTable'])->name('account-product-profits.dataTable')->middleware('permission:account-reports.view');
});
