<?php

use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\InventoryController as ManagerInventoryController;
use App\Http\Controllers\Manager\ProductController as ManagerProductController;
use App\Http\Controllers\Manager\ReportController;
use App\Http\Controllers\Manager\SaleController as ManagerSaleController;
use App\Http\Controllers\Manager\UserController as ManagerUserController;
use App\Http\Controllers\POS\AuthController;
use App\Http\Controllers\POS\CustomerController;
use App\Http\Controllers\POS\DashboardController;
use App\Http\Controllers\POS\ProductController;
use App\Http\Controllers\POS\SaleController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SubscriptionController;
use App\Http\Controllers\SuperAdmin\SubscriptionPlanController;
use App\Http\Controllers\SuperAdmin\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin API Routes
|--------------------------------------------------------------------------
|
| API routes for super admin panel to manage tenants and subscriptions
|
*/

Route::prefix('superadmin')->middleware(['auth:sanctum', 'superadmin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index']);

    // Tenant Management
    Route::apiResource('tenants', TenantController::class);
    Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend']);
    Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate']);

    // Subscription Management
    Route::apiResource('subscriptions', SubscriptionController::class);
    Route::post('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew']);
    Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);

    // Subscription Plan Management
    Route::apiResource('subscription-plans', SubscriptionPlanController::class);
}); /*
|--------------------------------------------------------------------------
| POS Mobile API Routes
|--------------------------------------------------------------------------
|
| API routes for the mobile POS application used by cashiers
|
*/

// Public routes
Route::prefix('pos')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (with tenant scoping and subscription check)
Route::prefix('pos')->middleware(['auth:sanctum', 'tenant.scope', 'subscription.active'])->group(function () {
    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/branches', [AuthController::class, 'branches']);
    Route::post('/switch-branch', [AuthController::class, 'switchBranch']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Products
    Route::get('/categories', [ProductController::class, 'categories']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{identifier}', [ProductController::class, 'show']);
    Route::post('/scan-barcode', [ProductController::class, 'scanBarcode']);

    // Combined endpoint for mobile (faster - one request instead of two)
    Route::get('/pos-data', [ProductController::class, 'posData']);

    // Image endpoints for offline caching (returns base64)
    Route::get('/image', [ProductController::class, 'image']);
    Route::post('/images', [ProductController::class, 'images']);

    // Sales
    Route::get('/sales', [SaleController::class, 'index']);
    Route::post('/sales', [SaleController::class, 'store']);
    Route::get('/sales/summary', [SaleController::class, 'summary']);
    Route::get('/sales/{sale}', [SaleController::class, 'show']);
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel']);
    Route::post('/sales/{sale}/customer', [SaleController::class, 'attachCustomer']);

    // Customers
    Route::post('/customers', [CustomerController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Manager Mobile API Routes
|--------------------------------------------------------------------------
|
| API routes for the mobile manager application used by branch managers
|
*/

// Manager routes (Protected with tenant scoping and subscription check)
Route::prefix('manager')->middleware(['auth:sanctum', 'tenant.scope', 'subscription.active'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [ManagerDashboardController::class, 'index']);

    // Sales Management
    Route::get('/sales', [ManagerSaleController::class, 'index']);
    Route::get('/sales/{sale}', [ManagerSaleController::class, 'show']);
    Route::post('/sales/{sale}/approve', [ManagerSaleController::class, 'approve']);
    Route::post('/sales/{sale}/void', [ManagerSaleController::class, 'void']);

    // Reports
    Route::get('/reports/sales', [ReportController::class, 'salesReport']);
    Route::get('/reports/cashiers', [ReportController::class, 'cashierReport']);
    Route::get('/reports/products', [ReportController::class, 'productReport']);
    Route::get('/reports/daily-summary', [ReportController::class, 'dailySummary']);
    Route::get('/reports/sales-summary', [ReportController::class, 'salesSummary']);

    // User Management (Cashiers in branch)
    Route::get('/users', [ManagerUserController::class, 'index']);
    Route::post('/users', [ManagerUserController::class, 'store']);
    Route::get('/users/{user}', [ManagerUserController::class, 'show']);
    Route::put('/users/{user}', [ManagerUserController::class, 'update']);
    Route::post('/users/{user}/toggle-status', [ManagerUserController::class, 'toggleStatus']);

    // Product Management
    Route::get('/products', [ManagerProductController::class, 'index']);
    Route::get('/products/{product}', [ManagerProductController::class, 'show']);
    Route::get('/products/{product}/sales', [ManagerProductController::class, 'sales']);
    Route::post('/products', [ManagerProductController::class, 'store']);
    Route::put('/products/{product}', [ManagerProductController::class, 'update']);
    Route::delete('/products/{product}', [ManagerProductController::class, 'destroy']);
    Route::get('/categories', [ManagerProductController::class, 'categories']);

    // Inventory Management
    Route::get('/inventory', [ManagerInventoryController::class, 'index']);
    Route::post('/inventory/{product}/adjust', [ManagerInventoryController::class, 'adjust']);
    Route::get('/inventory/low-stock', [ManagerInventoryController::class, 'lowStock']);
});
