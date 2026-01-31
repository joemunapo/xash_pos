<?php

namespace App\Http\Controllers\Web\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display cashier dashboard
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return Inertia::render('Cashier/Dashboard', [
                'stats' => null,
                'branch' => null,
                'message' => 'No branch assigned. Please contact your manager.',
            ]);
        }

        $today = now()->toDateString();

        // Base query - cashiers only see their own sales
        $baseQuery = Sale::where('branch_id', $branch->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->where('user_id', $user->id);

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

        // Recent sales (last 5)
        $recentSales = (clone $baseQuery)
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'receipt_number', 'total_amount', 'payment_method', 'created_at']);

        return Inertia::render('Cashier/Dashboard', [
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
            'recentSales' => $recentSales,
        ]);
    }
}
