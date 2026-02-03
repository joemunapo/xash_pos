<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Get sales report
     */
    public function salesReport(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
            ]);
        }

        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $sales = Sale::where('branch_id', $branch->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_sale
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($sales);
    }

    /**
     * Get cashier performance report
     */
    public function cashierReport(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
            ]);
        }

        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $cashiers = Sale::where('branch_id', $branch->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                user_id,
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_sale
            ')
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'cashier_id' => $item->user_id,
                    'cashier_name' => $item->user->name ?? 'Unknown',
                    'total_sales' => (int) $item->total_sales,
                    'total_revenue' => (float) $item->total_revenue,
                    'average_sale' => (float) $item->average_sale,
                ];
            });

        return response()->json($cashiers);
    }

    /**
     * Get product sales report
     */
    public function productReport(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
            ]);
        }

        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $products = \DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.branch_id', $branch->id)
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->selectRaw('
                products.id,
                products.name,
                products.sku,
                SUM(sale_items.quantity) as total_quantity,
                SUM(sale_items.line_total) as total_revenue
            ')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_quantity')
            ->limit(50)
            ->get();

        return response()->json($products);
    }

    /**
     * Get daily summary
     */
    public function dailySummary(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
            ]);
        }

        $date = $request->date ?? now()->toDateString();

        $summary = Sale::where('branch_id', $branch->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereDate('created_at', $date)
            ->selectRaw('
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_sale,
                payment_method,
                COUNT(*) as count
            ')
            ->groupBy('payment_method')
            ->get();

        return response()->json($summary);
    }

    /**
     * Get sales summary report with metrics and daily breakdown
     */
    public function salesSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
            ]);
        }

        // Set default date range (start of current month)
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        $startTime = $request->start_time ?? '00:00:00';
        $endTime = $request->end_time ?? '23:59:59';

        // Get accessible branches (for multi-branch support)
        $branchIds = $request->branch_ids
            ? (array) $request->branch_ids
            : [$branch->id];

        // Get cashier IDs if specified
        $cashierIds = $request->cashier_ids ? (array) $request->cashier_ids : null;

        // Get product IDs if specified
        $productIds = $request->product_ids ? (array) $request->product_ids : null;

        // Build base query for completed sales
        $completedQuery = Sale::query()
            ->whereIn('branch_id', $branchIds)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate.' '.$startTime, $endDate.' '.$endTime]);

        if ($cashierIds) {
            $completedQuery->whereIn('user_id', $cashierIds);
        }

        // Filter by products if specified
        if ($productIds) {
            $completedQuery->whereHas('saleItems', function ($q) use ($productIds) {
                $q->whereIn('product_id', $productIds);
            });
        }

        // Build query for refunded sales
        $refundedQuery = Sale::query()
            ->whereIn('branch_id', $branchIds)
            ->where('status', Sale::STATUS_REFUNDED)
            ->whereBetween('created_at', [$startDate.' '.$startTime, $endDate.' '.$endTime]);

        if ($cashierIds) {
            $refundedQuery->whereIn('user_id', $cashierIds);
        }

        // Filter by products for refunds too
        if ($productIds) {
            $refundedQuery->whereHas('saleItems', function ($q) use ($productIds) {
                $q->whereIn('product_id', $productIds);
            });
        }

        // Calculate metrics for completed sales
        $completedMetrics = $completedQuery
            ->selectRaw('
                COALESCE(SUM(COALESCE(subtotal, 0) + COALESCE(tax_amount, 0)), 0) as gross_sales,
                COALESCE(SUM(COALESCE(discount_amount, 0)), 0) as discounts,
                COALESCE(SUM(total_amount), 0) as net_sales
            ')
            ->first();

        // Calculate refunds
        $refundMetrics = $refundedQuery
            ->selectRaw('COALESCE(SUM(total_amount), 0) as refunds')
            ->first();

        // Get daily breakdown for chart
        $dailyBreakdown = Sale::query()
            ->whereIn('branch_id', $branchIds)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate.' '.$startTime, $endDate.' '.$endTime])
            ->when($cashierIds, function ($q) use ($cashierIds) {
                return $q->whereIn('user_id', $cashierIds);
            })
            ->when($productIds, function ($q) use ($productIds) {
                return $q->whereHas('saleItems', function ($query) use ($productIds) {
                    $query->whereIn('product_id', $productIds);
                });
            })
            ->selectRaw('
                DATE(created_at) as date,
                COALESCE(SUM(total_amount), 0) as net_sales
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get available branches for filter
        $branches = \DB::table('branches')
            ->where('tenant_id', $branch->tenant_id)
            ->whereIn('id', $branchIds)
            ->select('id', 'name')
            ->get();

        // Get available cashiers for filter (all cashiers assigned to branch)
        $cashiers = \DB::table('branch_user')
            ->join('users', 'branch_user.user_id', '=', 'users.id')
            ->whereIn('branch_user.branch_id', $branchIds)
            ->where('branch_user.role', 'cashier')
            ->select('users.id', 'users.name')
            ->orderBy('users.name')
            ->get();

        // Get available products for filter
        $products = \DB::table('products')
            ->where('tenant_id', $user->tenant_id)
            ->select('id', 'name', 'sku')
            ->orderBy('name')
            ->get();

        // Calculate gross profits (Net Sales - Cost of Goods Sold)
        $grossProfitsQuery = \DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereIn('sales.branch_id', $branchIds)
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->whereBetween('sales.created_at', [$startDate.' '.$startTime, $endDate.' '.$endTime])
            ->when($cashierIds, function ($q) use ($cashierIds) {
                return $q->whereIn('sales.user_id', $cashierIds);
            })
            ->when($productIds, function ($q) use ($productIds) {
                return $q->whereIn('sale_items.product_id', $productIds);
            })
            ->selectRaw('
                COALESCE(SUM((sale_items.unit_price - COALESCE(sale_items.cost_price, 0)) * sale_items.quantity), 0) as gross_profits
            ')
            ->first();

        return response()->json([
            'metrics' => [
                'gross_sales' => (float) $completedMetrics->gross_sales,
                'discounts' => (float) $completedMetrics->discounts,
                'net_sales' => (float) $completedMetrics->net_sales,
                'refunds' => (float) $refundMetrics->refunds,
                'gross_profits' => (float) ($grossProfitsQuery->gross_profits ?? 0),
            ],
            'daily_breakdown' => $dailyBreakdown,
            'branches' => $branches,
            'cashiers' => $cashiers,
            'products' => $products,
        ]);
    }
}
