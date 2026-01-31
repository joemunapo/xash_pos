<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard stats for branch manager
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
                'stats' => null,
            ]);
        }

        $today = now()->toDateString();

        // Base query for all branch sales
        $baseQuery = Sale::where('branch_id', $branch->id)
            ->where('status', Sale::STATUS_COMPLETED);

        // Today's stats
        $todayStats = (clone $baseQuery)
            ->whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*) as total_sales,
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COALESCE(AVG(total_amount), 0) as average_sale
            ')
            ->first();

        // This week's stats
        $weekStart = now()->startOfWeek()->toDateString();
        $weekStats = (clone $baseQuery)
            ->whereDate('created_at', '>=', $weekStart)
            ->selectRaw('
                COUNT(*) as total_sales,
                COALESCE(SUM(total_amount), 0) as total_revenue
            ')
            ->first();

        // This month's stats
        $monthStart = now()->startOfMonth()->toDateString();
        $monthStats = (clone $baseQuery)
            ->whereDate('created_at', '>=', $monthStart)
            ->selectRaw('
                COUNT(*) as total_sales,
                COALESCE(SUM(total_amount), 0) as total_revenue
            ')
            ->first();

        // Recent sales (last 10)
        $recentSales = (clone $baseQuery)
            ->whereDate('created_at', $today)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'receipt_number', 'total_amount', 'payment_method', 'user_id', 'created_at']);

        // Hourly sales for today (for chart)
        $hourlySales = (clone $baseQuery)
            ->whereDate('created_at', $today)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total')
            ->groupByRaw('HOUR(created_at)')
            ->orderBy('hour')
            ->get();

        // Cashier performance today
        $cashierPerformance = (clone $baseQuery)
            ->whereDate('created_at', $today)
            ->selectRaw('user_id, COUNT(*) as sales_count, COALESCE(SUM(total_amount), 0) as total_amount')
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'cashier_name' => $item->user->name ?? 'Unknown',
                    'sales_count' => (int) $item->sales_count,
                    'total_amount' => (float) $item->total_amount,
                ];
            });

        // Active cashiers count
        $activeCashiers = User::where('role', 'cashier')
            ->whereHas('branches', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })
            ->where('is_active', true)
            ->count();

        return response()->json([
            'user' => [
                'name' => $user->name,
                'role' => $user->role,
            ],
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
            ],
            'today' => [
                'total_sales' => (int) $todayStats->total_sales,
                'total_revenue' => (float) $todayStats->total_revenue,
                'average_sale' => (float) $todayStats->average_sale,
            ],
            'week' => [
                'total_sales' => (int) $weekStats->total_sales,
                'total_revenue' => (float) $weekStats->total_revenue,
            ],
            'month' => [
                'total_sales' => (int) $monthStats->total_sales,
                'total_revenue' => (float) $monthStats->total_revenue,
            ],
            'recent_sales' => $recentSales,
            'hourly_sales' => $hourlySales,
            'cashier_performance' => $cashierPerformance,
            'active_cashiers' => $activeCashiers,
        ]);
    }
}
