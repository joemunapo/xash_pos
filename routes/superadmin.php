<?php

use App\Http\Controllers\SuperAdmin\Web\DashboardController;
use App\Http\Controllers\SuperAdmin\Web\SubscriptionPlanController;
use App\Http\Controllers\SuperAdmin\Web\TenantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'superadmin'])->prefix('superadmin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('superadmin.dashboard');

    // Tenant Management
    Route::get('/tenants', [TenantController::class, 'index'])->name('superadmin.tenants.index');
    Route::get('/tenants/create', [TenantController::class, 'create'])->name('superadmin.tenants.create');
    Route::post('/tenants', [TenantController::class, 'store'])->name('superadmin.tenants.store');
    Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('superadmin.tenants.show');
    Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('superadmin.tenants.update');
    Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('superadmin.tenants.destroy');
    Route::post('/tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('superadmin.tenants.suspend');
    Route::post('/tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('superadmin.tenants.activate');
    Route::post('/tenants/{tenant}/subscription', [TenantController::class, 'assignSubscription'])->name('superadmin.tenants.assign-subscription');

    // Subscription Plans
    Route::get('/plans', [SubscriptionPlanController::class, 'index'])->name('superadmin.plans.index');
    Route::get('/plans/create', [SubscriptionPlanController::class, 'create'])->name('superadmin.plans.create');
    Route::post('/plans', [SubscriptionPlanController::class, 'store'])->name('superadmin.plans.store');
    Route::get('/plans/{plan}/edit', [SubscriptionPlanController::class, 'edit'])->name('superadmin.plans.edit');
    Route::put('/plans/{plan}', [SubscriptionPlanController::class, 'update'])->name('superadmin.plans.update');
    Route::delete('/plans/{plan}', [SubscriptionPlanController::class, 'destroy'])->name('superadmin.plans.destroy');
});
