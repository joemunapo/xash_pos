<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    /**
     * Stock Overview Dashboard
     */
    public function overview(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Get tenant branches
        $tenantBranchIds = Branch::where('tenant_id', $tenantId)->pluck('id');

        // Get stock data with products
        $stockQuery = Stock::whereIn('branch_id', $tenantBranchIds)
            ->with(['product:id,name,sku,reorder_level,track_stock', 'branch:id,name'])
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            });

        $stocks = $stockQuery->latest()->paginate(20);

        // Get statistics
        $stats = [
            'total_products' => Product::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->where('track_stock', true)
                ->count(),
            'low_stock_items' => Stock::whereIn('branch_id', $tenantBranchIds)
                ->whereColumn('quantity', '<=', DB::raw('COALESCE((SELECT reorder_level FROM products WHERE products.id = stock.product_id), 0)'))
                ->count(),
            'out_of_stock' => Stock::whereIn('branch_id', $tenantBranchIds)
                ->where('quantity', '<=', 0)
                ->count(),
            'total_value' => Stock::whereIn('stock.branch_id', $tenantBranchIds)
                ->join('products', 'stock.product_id', '=', 'products.id')
                ->sum(DB::raw('stock.quantity * products.cost_price')),
        ];

        $branches = Branch::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Admin/Inventory/Overview', [
            'stocks' => $stocks,
            'stats' => $stats,
            'branches' => $branches,
            'filters' => $request->only(['search', 'branch_id']),
        ]);
    }

    /**
     * Low Stock Alerts
     */
    public function lowStock(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Get tenant branches
        $tenantBranchIds = Branch::where('tenant_id', $tenantId)->pluck('id');

        $lowStockItems = Stock::whereIn('stock.branch_id', $tenantBranchIds)
            ->join('products', 'stock.product_id', '=', 'products.id')
            ->whereColumn('stock.quantity', '<=', 'products.reorder_level')
            ->where('products.track_stock', true)
            ->where('products.is_active', true)
            ->with(['product', 'branch'])
            ->select('stock.*')
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('stock.branch_id', $branchId);
            })
            ->orderBy('stock.quantity', 'asc')
            ->paginate(20);

        $branches = Branch::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Admin/Inventory/LowStock', [
            'lowStockItems' => $lowStockItems,
            'branches' => $branches,
            'filters' => $request->only(['branch_id']),
        ]);
    }

    /**
     * Expiring Items
     */
    public function expiring(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $daysAhead = $request->days ?? 30;

        // Get tenant branches
        $tenantBranchIds = Branch::where('tenant_id', $tenantId)->pluck('id');

        $expiringBatches = Batch::whereIn('batches.branch_id', $tenantBranchIds)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($daysAhead))
            ->where('quantity_remaining', '>', 0)
            ->with(['product', 'branch'])
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('batches.branch_id', $branchId);
            })
            ->orderBy('expiry_date', 'asc')
            ->paginate(20);

        $branches = Branch::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Admin/Inventory/Expiring', [
            'expiringBatches' => $expiringBatches,
            'branches' => $branches,
            'filters' => $request->only(['branch_id', 'days']),
        ]);
    }

    /**
     * Stock Adjustments
     */
    public function adjustments(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Get tenant branches
        $tenantBranchIds = Branch::where('tenant_id', $tenantId)->pluck('id');

        $adjustments = StockMovement::whereIn('stock_movements.branch_id', $tenantBranchIds)
            ->where('type', 'adjustment')
            ->with(['product', 'branch', 'user'])
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where('stock_movements.branch_id', $branchId);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('stock_movements.created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('stock_movements.created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate(20);

        $branches = Branch::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('track_stock', true)
            ->get(['id', 'name', 'sku']);

        return Inertia::render('Admin/Inventory/Adjustments', [
            'adjustments' => $adjustments,
            'branches' => $branches,
            'products' => $products,
            'filters' => $request->only(['branch_id', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Create Stock Adjustment
     */
    public function createAdjustment(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'quantity' => ['required', 'numeric'],
            'reason' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $user) {
            // Get or create stock record
            $stock = Stock::firstOrCreate(
                [
                    'product_id' => $validated['product_id'],
                    'branch_id' => $validated['branch_id'],
                ],
                [
                    'quantity' => 0,
                ]
            );

            $oldQuantity = $stock->quantity;
            $stock->quantity += $validated['quantity'];
            $stock->save();

            // Record the movement
            StockMovement::create([
                'product_id' => $validated['product_id'],
                'branch_id' => $validated['branch_id'],
                'user_id' => $user->id,
                'type' => 'adjustment',
                'quantity' => $validated['quantity'],
                'balance_after' => $stock->quantity,
                'reference' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return back()->with('success', 'Stock adjustment recorded successfully.');
    }

    /**
     * Stock Transfers
     */
    public function transfers(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Get tenant branches
        $tenantBranchIds = Branch::where('tenant_id', $tenantId)->pluck('id');

        $transfers = StockMovement::whereIn('stock_movements.branch_id', $tenantBranchIds)
            ->where('type', 'transfer')
            ->with(['product', 'branch', 'toBranch', 'user'])
            ->when($request->branch_id, function ($query, $branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('stock_movements.branch_id', $branchId)
                        ->orWhere('stock_movements.to_branch_id', $branchId);
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('stock_movements.status', $status);
            })
            ->latest()
            ->paginate(20);

        $branches = Branch::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('track_stock', true)
            ->get(['id', 'name', 'sku']);

        return Inertia::render('Admin/Inventory/Transfers', [
            'transfers' => $transfers,
            'branches' => $branches,
            'products' => $products,
            'filters' => $request->only(['branch_id', 'status']),
        ]);
    }

    /**
     * Create Stock Transfer
     */
    public function createTransfer(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'from_branch_id' => ['required', 'exists:branches,id'],
            'to_branch_id' => ['required', 'exists:branches,id', 'different:from_branch_id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $user) {
            // Check source branch has enough stock
            $sourceStock = Stock::where('product_id', $validated['product_id'])
                ->where('branch_id', $validated['from_branch_id'])
                ->first();

            if (! $sourceStock || $sourceStock->quantity < $validated['quantity']) {
                throw new \Exception('Insufficient stock in source branch.');
            }

            // Deduct from source
            $sourceStock->decrement('quantity', $validated['quantity']);

            // Add to destination
            $destStock = Stock::firstOrCreate(
                [
                    'product_id' => $validated['product_id'],
                    'branch_id' => $validated['to_branch_id'],
                ],
                [
                    'quantity' => 0,
                ]
            );
            $destStock->increment('quantity', $validated['quantity']);

            // Record the movement
            StockMovement::create([
                'product_id' => $validated['product_id'],
                'branch_id' => $validated['from_branch_id'],
                'user_id' => $user->id,
                'type' => StockMovement::TYPE_TRANSFER_OUT,
                'quantity' => -$validated['quantity'],
                'balance_after' => $sourceStock->quantity,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Record destination movement
            StockMovement::create([
                'product_id' => $validated['product_id'],
                'branch_id' => $validated['to_branch_id'],
                'user_id' => $user->id,
                'type' => StockMovement::TYPE_TRANSFER_IN,
                'quantity' => $validated['quantity'],
                'balance_after' => $destStock->quantity,
                'reference' => 'Transfer from Branch ID: '.$validated['from_branch_id'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return back()->with('success', 'Stock transfer completed successfully.');
    }
}
