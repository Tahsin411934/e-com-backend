<?php

use Modules\Account\Http\Controllers\AccountInvestmentController;
use Illuminate\Support\Facades\Route;
use Modules\Account\Http\Controllers\AccountAccountController;
use Modules\Account\Http\Controllers\AccountCategoryController;
use Modules\Account\Http\Controllers\AccountController;
use Modules\Account\Http\Controllers\AccountExpenseController;
use Modules\Account\Http\Controllers\AccountReportController;
use Modules\Account\Http\Controllers\AccountTransferController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('account', [AccountController::class, 'index'])->name('account.dashboard');

    Route::resource('account-accounts', AccountAccountController::class)->except(['create', 'edit'])->names('account-accounts');
    Route::get('/dataTable/account-accounts', [AccountAccountController::class, 'dataTable'])->name('account-accounts.dataTable');

    Route::resource('account-categories', AccountCategoryController::class)->except(['create', 'edit'])->names('account-categories');
    Route::get('/dataTable/account-categories', [AccountCategoryController::class, 'dataTable'])->name('account-categories.dataTable');

    Route::resource('account-expenses', AccountExpenseController::class)->except(['create', 'edit'])->names('account-expenses');
    Route::get('/dataTable/account-expenses', [AccountExpenseController::class, 'dataTable'])->name('account-expenses.dataTable');

    Route::resource('account-investments', AccountInvestmentController::class)->except(['create', 'edit'])->names('account-investments');
    Route::get('/dataTable/account-investments', [AccountInvestmentController::class, 'dataTable'])->name('account-investments.dataTable');

    Route::resource('account-transfers', AccountTransferController::class)->only(['index', 'store'])->names('account-transfers');
    Route::get('/dataTable/account-transfers', [AccountTransferController::class, 'dataTable'])->name('account-transfers.dataTable');

    Route::get('account-transactions', [AccountReportController::class, 'transactions'])->name('account-transactions.index');
    Route::get('/dataTable/account-transactions', [AccountReportController::class, 'transactionsDataTable'])->name('account-transactions.dataTable');

    Route::get('account-product-profits', [AccountReportController::class, 'productProfits'])->name('account-product-profits.index');
    Route::get('/dataTable/account-product-profits', [AccountReportController::class, 'productProfitsDataTable'])->name('account-product-profits.dataTable');
});