<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;

        // Get basic stats
        $stats = [
            'total_branches' => Branch::where('company_id', $companyId)->count(),
            'active_branches' => Branch::where('company_id', $companyId)->where('is_active', true)->count(),
            'total_products' => Product::where('company_id', $companyId)->count(),
            'active_products' => Product::where('company_id', $companyId)->where('is_active', true)->count(),
            'total_users' => User::where('company_id', $companyId)->count(),
            'active_users' => User::where('company_id', $companyId)->where('is_active', true)->count(),
            'total_customers' => Customer::where('company_id', $companyId)->count(),
        ];

        // Get total stock value
        $totalStockValue = Stock::join('products', 'stock.product_id', '=', 'products.id')
            ->where('products.company_id', $companyId)
            ->sum(DB::raw('stock.quantity * products.selling_price'));

        $stats['total_stock_value'] = $totalStockValue;

        // Get low stock count
        $lowStockCount = Stock::join('products', 'stock.product_id', '=', 'products.id')
            ->where('products.company_id', $companyId)
            ->whereColumn('stock.quantity', '<=', 'products.reorder_level')
            ->count();

        $stats['low_stock_count'] = $lowStockCount;

        // Get recent branches
        $branches = Branch::where('company_id', $companyId)
            ->latest()
            ->take(5)
            ->get();

        // Get recent users
        $recentUsers = User::where('company_id', $companyId)
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'email', 'role', 'is_active', 'created_at']);

        // Get products growth over last 7 days
        $productsGrowth = Product::where('company_id', $companyId)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Fill in missing days with 0
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $last7Days[] = [
                'date' => Carbon::parse($date)->format('M d'),
                'count' => $productsGrowth[$date] ?? 0
            ];
        }

        // Get category distribution
        $categoryDistribution = Category::where('company_id', $companyId)
            ->withCount('products')
            ->having('products_count', '>', 0)
            ->orderByDesc('products_count')
            ->take(6)
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'count' => $category->products_count
                ];
            });

        // Get top products by stock value
        $topProducts = Product::where('products.company_id', $companyId)
            ->leftJoin('stock', 'products.id', '=', 'stock.product_id')
            ->select('products.name', 'products.selling_price as price', 'products.sku')
            ->selectRaw('SUM(COALESCE(stock.quantity, 0)) as total_quantity')
            ->selectRaw('SUM(COALESCE(stock.quantity, 0) * products.selling_price) as stock_value')
            ->groupBy('products.id', 'products.name', 'products.selling_price', 'products.sku')
            ->orderByDesc('stock_value')
            ->take(5)
            ->get();

        // Get stock levels summary
        $stockLevels = [
            'in_stock' => Stock::join('products', 'stock.product_id', '=', 'products.id')
                ->where('products.company_id', $companyId)
                ->where('stock.quantity', '>', DB::raw('products.reorder_level'))
                ->count(),
            'low_stock' => $lowStockCount,
            'out_of_stock' => Product::where('company_id', $companyId)
                ->whereDoesntHave('stock')
                ->orWhereHas('stock', function ($query) {
                    $query->where('quantity', 0);
                })
                ->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'branches' => $branches,
            'recentUsers' => $recentUsers,
            'lowStockCount' => $lowStockCount,
            'productsGrowth' => $last7Days,
            'categoryDistribution' => $categoryDistribution,
            'topProducts' => $topProducts,
            'stockLevels' => $stockLevels,
        ]);
    }
}
