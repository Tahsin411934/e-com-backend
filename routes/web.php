<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Dashboard: platform staff + SaaS store owners.
// NOTE: store-scoped dashboard data lands in the next phase (Phase 3).
Route::middleware(['auth', 'verified', 'admin', 'role:Super Admin,Admin,Store Owner'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/data', [DashboardController::class, 'apiData'])->name('dashboard.api');
});

Route::middleware(['auth', 'verified', 'admin', 'role:Super Admin,Admin,Manager,Staff,Store Owner,Store Staff'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
