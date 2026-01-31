<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SalesController extends Controller
{
    /**
     * Sales History
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $companyBranchIds = Branch::where('company_id', $companyId)->pluck('id');

        $sales = Sale::whereIn('branch_id', $companyBranchIds)
            ->with(['branch:id,name', 'user:id,name', 'customer:id,name,phone'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('receipt_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($request->payment_method, function ($query, $method) {
                $query->where('payment_method', $method);
            })
            ->latest()
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $branches = Branch::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        // Get summary statistics
        $stats = [
            'total_sales' => (int) Sale::whereIn('branch_id', $companyBranchIds)
                ->where('status', Sale::STATUS_COMPLETED)
                ->count(),
            'total_revenue' => (float) Sale::whereIn('branch_id', $companyBranchIds)
                ->where('status', Sale::STATUS_COMPLETED)
                ->sum('total_amount') ?: 0,
            'today_sales' => (int) Sale::whereIn('branch_id', $companyBranchIds)
                ->where('status', Sale::STATUS_COMPLETED)
                ->whereDate('created_at', today())
                ->count(),
            'today_revenue' => (float) Sale::whereIn('branch_id', $companyBranchIds)
                ->where('status', Sale::STATUS_COMPLETED)
                ->whereDate('created_at', today())
                ->sum('total_amount') ?: 0,
        ];

        return Inertia::render('Admin/Sales/Index', [
            'sales' => $sales,
            'branches' => $branches,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'branch_id', 'date_from', 'date_to', 'payment_method']),
        ]);
    }

    /**
     * Show sale details
     */
    public function show(Sale $sale): Response
    {
        $this->authorizeSale($sale);

        $sale->load(['items.product', 'branch', 'user', 'customer']);

        return Inertia::render('Admin/Sales/Show', [
            'sale' => $sale,
        ]);
    }

    /**
     * Daily Summary
     */
    public function dailySummary(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $date = $request->date ?? today()->toDateString();
        $companyBranchIds = Branch::where('company_id', $companyId)->pluck('id');

        // Sales by branch
        $salesByBranch = Sale::whereIn('branch_id', $companyBranchIds)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereDate('created_at', $date)
            ->select('branch_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('branch_id')
            ->with('branch:id,name')
            ->get();

        // Sales by payment method
        $salesByPayment = Sale::whereIn('branch_id', $companyBranchIds)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereDate('created_at', $date)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('payment_method')
            ->get();

        // Hourly breakdown
        $hourlySales = Sale::whereIn('branch_id', $companyBranchIds)
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereDate('created_at', $date)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Top selling products
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereIn('sales.branch_id', $companyBranchIds)
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->whereDate('sales.created_at', $date)
            ->select('sale_items.product_name', DB::raw('SUM(sale_items.quantity) as total_quantity'), DB::raw('SUM(sale_items.line_total) as total_revenue'))
            ->groupBy('sale_items.product_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        $summary = [
            'total_sales' => (int) Sale::whereIn('branch_id', $companyBranchIds)
                ->where('status', Sale::STATUS_COMPLETED)
                ->whereDate('created_at', $date)
                ->count(),
            'total_revenue' => (float) Sale::whereIn('branch_id', $companyBranchIds)
                ->where('status', Sale::STATUS_COMPLETED)
                ->whereDate('created_at', $date)
                ->sum('total_amount') ?: 0,
            'total_tax' => (float) Sale::whereIn('branch_id', $companyBranchIds)
                ->where('status', Sale::STATUS_COMPLETED)
                ->whereDate('created_at', $date)
                ->sum('tax_amount') ?: 0,
            'total_discount' => (float) Sale::whereIn('branch_id', $companyBranchIds)
                ->where('status', Sale::STATUS_COMPLETED)
                ->whereDate('created_at', $date)
                ->sum('discount_amount') ?: 0,
        ];

        $branches = Branch::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Admin/Sales/DailySummary', [
            'summary' => $summary,
            'salesByBranch' => $salesByBranch,
            'salesByPayment' => $salesByPayment,
            'hourlySales' => $hourlySales,
            'topProducts' => $topProducts,
            'branches' => $branches,
            'date' => $date,
        ]);
    }

    /**
     * Refunds
     */
    public function refunds(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $companyBranchIds = Branch::where('company_id', $companyId)->pluck('id');

        $refunds = Sale::whereIn('branch_id', $companyBranchIds)
            ->where('status', Sale::STATUS_REFUNDED)
            ->with(['branch:id,name', 'user:id,name', 'customer:id,name,phone'])
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $branches = Branch::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Admin/Sales/Refunds', [
            'refunds' => $refunds,
            'branches' => $branches,
            'filters' => $request->only(['branch_id', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Authorize sale access
     */
    private function authorizeSale(Sale $sale)
    {
        $user = auth()->user();
        $companyBranchIds = Branch::where('company_id', $user->company_id)->pluck('id');

        if (!$companyBranchIds->contains($sale->branch_id)) {
            abort(403, 'Unauthorized access to sale.');
        }
    }
}
