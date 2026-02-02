<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportsController extends Controller
{
    /**
     * Sales Reports
     */
    public function sales(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfDay();

        // Daily sales for chart
        $dailySales = Sale::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(total_amount - COALESCE(discount_amount, 0)) as net_sales')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Summary stats
        $summary = [
            'total_sales' => Sale::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount'),
            'total_transactions' => Sale::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'average_transaction' => Sale::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->avg('total_amount') ?? 0,
            'total_discounts' => Sale::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('discount_amount') ?? 0,
        ];

        // Top products
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.tenant_id', $tenantId)
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.line_total) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // Sales by payment method
        $salesByPayment = Sale::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        $branches = Branch::where('tenant_id', $tenantId)->get(['id', 'name']);

        return Inertia::render('Admin/Reports/Sales', [
            'dailySales' => $dailySales,
            'summary' => $summary,
            'topProducts' => $topProducts,
            'salesByPayment' => $salesByPayment,
            'branches' => $branches,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'branch_id' => $request->branch_id,
            ],
        ]);
    }

    /**
     * Branch Comparison Report
     */
    public function branchComparison(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfDay();

        $branches = Branch::where('tenant_id', $tenantId)
            ->withCount(['sales as total_transactions' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function ($branch) use ($startDate, $endDate) {
                $sales = Sale::where('branch_id', $branch->id)
                    ->whereBetween('created_at', [$startDate, $endDate]);

                $branch->total_sales = $sales->sum('total_amount');
                $branch->average_sale = $sales->avg('total_amount') ?? 0;
                $branch->total_items = DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->where('sales.branch_id', $branch->id)
                    ->whereBetween('sales.created_at', [$startDate, $endDate])
                    ->sum('sale_items.quantity');

                return $branch;
            });

        return Inertia::render('Admin/Reports/BranchComparison', [
            'branches' => $branches,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Employee Performance Report
     */
    public function employeePerformance(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfDay();

        $allEmployees = User::where('tenant_id', $tenantId)
            ->where('role', '!=', 'admin')
            ->get(['id', 'name', 'role', 'email']);

        $employeeQuery = User::where('tenant_id', $tenantId)
            ->where('role', '!=', 'admin')
            ->when($request->employee_id, function ($query, $employeeId) {
                $query->where('id', $employeeId);
            });

        $employees = $employeeQuery->get()
            ->map(function ($employee) use ($startDate, $endDate) {
                $sales = Sale::where('user_id', $employee->id)
                    ->whereBetween('created_at', [$startDate, $endDate]);

                $employee->total_sales = $sales->sum('total_amount');
                $employee->total_transactions = $sales->count();
                $employee->average_sale = $sales->avg('total_amount') ?? 0;
                $employee->total_items = DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->where('sales.user_id', $employee->id)
                    ->whereBetween('sales.created_at', [$startDate, $endDate])
                    ->sum('sale_items.quantity');

                return $employee;
            })
            ->sortByDesc('total_sales')
            ->values();

        $branches = Branch::where('tenant_id', $tenantId)->get(['id', 'name']);

        return Inertia::render('Admin/Reports/EmployeePerformance', [
            'employees' => $employees,
            'allEmployees' => $allEmployees,
            'branches' => $branches,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'branch_id' => $request->branch_id,
                'employee_id' => $request->employee_id,
            ],
        ]);
    }

    /**
     * Inventory Reports
     */
    public function inventory(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Stock value by category
        $stockByCategory = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('stock', 'products.id', '=', 'stock.product_id')
            ->where('products.tenant_id', $tenantId)
            ->select(
                'categories.name as category',
                DB::raw('COALESCE(SUM(stock.quantity), 0) as total_quantity'),
                DB::raw('COALESCE(SUM(stock.quantity * products.cost_price), 0) as stock_value')
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();

        // Low stock products
        $lowStockProducts = Product::where('tenant_id', $tenantId)
            ->whereHas('stock', function ($query) {
                $query->whereRaw('stock.quantity <= products.reorder_level');
            })
            ->with(['category:id,name', 'stock'])
            ->limit(20)
            ->get();

        // Stock movement summary
        $stockMovements = DB::table('stock_movements')
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->where('products.tenant_id', $tenantId)
            ->where('stock_movements.created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                'stock_movements.type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(ABS(stock_movements.quantity)) as total_quantity')
            )
            ->groupBy('stock_movements.type')
            ->get();

        // Total stock value
        $totalStockValue = DB::table('products')
            ->leftJoin('stock', 'products.id', '=', 'stock.product_id')
            ->where('products.tenant_id', $tenantId)
            ->sum(DB::raw('COALESCE(stock.quantity, 0) * products.cost_price'));

        $branches = Branch::where('tenant_id', $tenantId)->get(['id', 'name']);

        return Inertia::render('Admin/Reports/Inventory', [
            'stockByCategory' => $stockByCategory,
            'lowStockProducts' => $lowStockProducts,
            'stockMovements' => $stockMovements,
            'totalStockValue' => $totalStockValue,
            'branches' => $branches,
            'filters' => [
                'branch_id' => $request->branch_id,
            ],
        ]);
    }

    /**
     * Financial Reports
     */
    public function financial(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfDay();

        // Revenue summary
        $revenue = Sale::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        // Cost of goods sold (simplified)
        $costOfGoods = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.tenant_id', $tenantId)
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->sum(DB::raw('sale_items.quantity * products.cost_price'));

        // Gross profit
        $grossProfit = $revenue - $costOfGoods;
        $grossMargin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;

        // Monthly revenue trend
        $monthlyRevenue = Sale::where('tenant_id', $tenantId)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Revenue by payment method
        $revenueByPayment = Sale::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        $branches = Branch::where('tenant_id', $tenantId)->get(['id', 'name']);

        return Inertia::render('Admin/Reports/Financial', [
            'summary' => [
                'revenue' => $revenue,
                'cost_of_goods' => $costOfGoods,
                'gross_profit' => $grossProfit,
                'gross_margin' => round($grossMargin, 2),
            ],
            'monthlyRevenue' => $monthlyRevenue,
            'revenueByPayment' => $revenueByPayment,
            'branches' => $branches,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'branch_id' => $request->branch_id,
            ],
        ]);
    }
}
