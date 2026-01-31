<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GoodsReceivedController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\TaxController;
use App\Http\Controllers\Admin\UnitOfMeasureController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('admin.activity-logs.show');

    // Inventory Management
    Route::prefix('inventory')->group(function () {
        Route::get('/overview', [InventoryController::class, 'overview'])->name('admin.inventory.overview');
        Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('admin.inventory.low-stock');
        Route::get('/expiring', [InventoryController::class, 'expiring'])->name('admin.inventory.expiring');
        Route::get('/adjustments', [InventoryController::class, 'adjustments'])->name('admin.inventory.adjustments');
        Route::post('/adjustments', [InventoryController::class, 'createAdjustment'])->name('admin.inventory.adjustments.store');
        Route::get('/transfers', [InventoryController::class, 'transfers'])->name('admin.inventory.transfers');
        Route::post('/transfers', [InventoryController::class, 'createTransfer'])->name('admin.inventory.transfers.store');
    });

    // Suppliers
    Route::resource('suppliers', SupplierController::class)->names([
        'index' => 'admin.suppliers.index',
        'create' => 'admin.suppliers.create',
        'store' => 'admin.suppliers.store',
        'show' => 'admin.suppliers.show',
        'edit' => 'admin.suppliers.edit',
        'update' => 'admin.suppliers.update',
        'destroy' => 'admin.suppliers.destroy',
    ]);

    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class)->names([
        'index' => 'admin.purchase-orders.index',
        'create' => 'admin.purchase-orders.create',
        'store' => 'admin.purchase-orders.store',
        'show' => 'admin.purchase-orders.show',
        'edit' => 'admin.purchase-orders.edit',
        'update' => 'admin.purchase-orders.update',
        'destroy' => 'admin.purchase-orders.destroy',
    ]);

    // Goods Received
    Route::get('/goods-received', [GoodsReceivedController::class, 'index'])->name('admin.goods-received.index');

    // Sales
    Route::get('/sales', [SalesController::class, 'index'])->name('admin.sales.index');
    Route::get('/sales/{sale}', [SalesController::class, 'show'])->name('admin.sales.show');
    Route::get('/sales-summary', [SalesController::class, 'dailySummary'])->name('admin.sales.daily-summary');
    Route::get('/sales-refunds', [SalesController::class, 'refunds'])->name('admin.sales.refunds');

    // Branches
    Route::resource('branches', BranchController::class)->names([
        'index' => 'admin.branches.index',
        'create' => 'admin.branches.create',
        'store' => 'admin.branches.store',
        'show' => 'admin.branches.show',
        'edit' => 'admin.branches.edit',
        'update' => 'admin.branches.update',
        'destroy' => 'admin.branches.destroy',
    ]);

    // Users
    Route::resource('users', UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);
    Route::post('users/{user}/reset-pin', [UserController::class, 'resetPin'])->name('admin.users.reset-pin');

    // Products
    Route::resource('products', ProductController::class)->names([
        'index' => 'admin.products.index',
        'create' => 'admin.products.create',
        'store' => 'admin.products.store',
        'show' => 'admin.products.show',
        'edit' => 'admin.products.edit',
        'update' => 'admin.products.update',
        'destroy' => 'admin.products.destroy',
    ]);
    // Allow POST with method spoofing for file uploads
    Route::post('products/{product}', [ProductController::class, 'update'])->name('admin.products.update');

    // Categories
    Route::resource('categories', CategoryController::class)->names([
        'index' => 'admin.categories.index',
        'create' => 'admin.categories.create',
        'store' => 'admin.categories.store',
        'edit' => 'admin.categories.edit',
        'update' => 'admin.categories.update',
        'destroy' => 'admin.categories.destroy',
    ]);

    // Units of Measure
    Route::get('units', [UnitOfMeasureController::class, 'index'])->name('admin.units.index');
    Route::post('units', [UnitOfMeasureController::class, 'store'])->name('admin.units.store');
    Route::put('units/{unit}', [UnitOfMeasureController::class, 'update'])->name('admin.units.update');
    Route::delete('units/{unit}', [UnitOfMeasureController::class, 'destroy'])->name('admin.units.destroy');
    Route::post('units/conversions', [UnitOfMeasureController::class, 'storeConversion'])->name('admin.units.conversions.store');
    Route::delete('units/conversions/{conversion}', [UnitOfMeasureController::class, 'destroyConversion'])->name('admin.units.conversions.destroy');

    // Customers
    Route::resource('customers', CustomerController::class)->names([
        'index' => 'admin.customers.index',
        'create' => 'admin.customers.create',
        'store' => 'admin.customers.store',
        'show' => 'admin.customers.show',
        'edit' => 'admin.customers.edit',
        'update' => 'admin.customers.update',
        'destroy' => 'admin.customers.destroy',
    ]);
    Route::post('customers/{customer}/adjust-points', [CustomerController::class, 'adjustPoints'])->name('admin.customers.adjust-points');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::put('/settings/company', [SettingsController::class, 'updateCompany'])->name('admin.settings.company');
    Route::post('/settings/logo', [SettingsController::class, 'updateLogo'])->name('admin.settings.logo');
    Route::get('/settings/payment-methods', [SettingsController::class, 'paymentMethods'])->name('admin.settings.payment-methods');
    Route::put('/settings/payment-methods', [SettingsController::class, 'updatePaymentMethods'])->name('admin.settings.payment-methods.update');
    
    // Tax Management
    Route::get('/settings/tax', [TaxController::class, 'index'])->name('admin.settings.tax');
    Route::post('/settings/tax', [TaxController::class, 'store'])->name('admin.settings.tax.store');
    Route::put('/settings/tax/{tax}', [TaxController::class, 'update'])->name('admin.settings.tax.update');
    Route::delete('/settings/tax/{tax}', [TaxController::class, 'destroy'])->name('admin.settings.tax.destroy');
    
    Route::get('/settings/receipt', [SettingsController::class, 'receiptSettings'])->name('admin.settings.receipt');
    Route::put('/settings/receipt', [SettingsController::class, 'updateReceiptSettings'])->name('admin.settings.receipt.update');
});
