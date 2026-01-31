<?php

use App\Http\Controllers\Web\Cashier\DashboardController;
use App\Http\Controllers\Web\Cashier\POSController;
use App\Http\Controllers\Web\Cashier\SaleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:cashier'])
    ->prefix('cashier')
    ->name('cashier.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // POS Terminal (to be implemented in Phase 5)
        Route::get('/pos', [POSController::class, 'index'])->name('pos');
        Route::post('/pos/process-sale', [POSController::class, 'processSale'])->name('pos.process');

        // Sales History
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    });
