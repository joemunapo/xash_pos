<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Get sales history for the current user/branch
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
                'sales' => [],
            ]);
        }

        $query = Sale::where('branch_id', $branch->id)
            ->with(['items', 'customer:id,name,phone', 'branch.tenant:id,name', 'user:id,name'])
            ->orderByDesc('created_at');

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            // Default to today
            $query->whereDate('created_at', now()->toDateString());
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user (for managers/admins)
        if ($request->filled('user_id') && ($user->isManager() || $user->isAdmin())) {
            $query->where('user_id', $request->user_id);
        } else {
            // Cashiers only see their own sales
            if ($user->isCashier()) {
                $query->where('user_id', $user->id);
            }
        }

        $sales = $query->paginate($request->input('per_page', 20));

        return response()->json($sales);
    }

    /**
     * Create a new sale
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:cash,card,mobile_money,split'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned. Cannot create sale.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Generate receipt number
            $receiptNumber = Sale::generateReceiptNumber($branch->id);

            // Create sale
            $sale = Sale::create([
                'tenant_id' => $user->tenant_id,
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'customer_id' => $request->customer_id,
                'receipt_number' => $receiptNumber,
                'discount_amount' => $request->discount_amount ?? 0,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'amount_paid' => $request->amount_paid,
                'notes' => $request->notes,
                'status' => Sale::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            $subtotal = 0;
            $totalTax = 0;

            // Create sale items and update stock
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $itemDiscount = $item['discount_amount'] ?? 0;
                $lineTotal = ($unitPrice * $quantity) - $itemDiscount;

                // Calculate tax if applicable
                $taxAmount = 0;
                if ($product->is_taxable && $product->tax_rate > 0) {
                    $taxAmount = $lineTotal * ($product->tax_rate / 100);
                }

                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'variant_id' => $item['variant_id'] ?? null,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit' => $item['unit'] ?? $product->unit,
                    'unit_price' => $unitPrice,
                    'cost_price' => $product->cost_price,
                    'discount_amount' => $itemDiscount,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                ]);

                $subtotal += $lineTotal;
                $totalTax += $taxAmount;

                // Update stock if tracking is enabled
                if ($product->track_stock) {
                    $stock = Stock::firstOrCreate(
                        [
                            'branch_id' => $branch->id,
                            'product_id' => $product->id,
                            'variant_id' => $item['variant_id'] ?? null,
                        ],
                        [
                            'quantity' => 0,
                            'reserved_quantity' => 0,
                        ]
                    );

                    $stock->decrement('quantity', $quantity);

                    // Record stock movement
                    StockMovement::create([
                        'branch_id' => $branch->id,
                        'product_id' => $product->id,
                        'variant_id' => $item['variant_id'] ?? null,
                        'user_id' => $user->id,
                        'type' => 'sale',
                        'quantity' => -$quantity,
                        'balance_after' => $stock->quantity,
                        'reference' => "SALE-{$sale->id}",
                        'notes' => "Sale #{$receiptNumber}",
                    ]);
                }
            }

            // Update sale totals
            $sale->update([
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'total_amount' => $subtotal + $totalTax - ($request->discount_amount ?? 0),
                'change_amount' => max(0, $request->amount_paid - ($subtotal + $totalTax - ($request->discount_amount ?? 0))),
            ]);

            DB::commit();

            // Clear product cache to reflect updated stock levels
            cache()->forget("pos_all_data_{$user->tenant_id}_{$branch->id}");
            cache()->forget("pos_products_{$user->tenant_id}_{$branch->id}_all_none");

            // Load relationships for response
            $sale->load(['items.product:id,name,sku', 'customer:id,name,phone', 'branch.tenant']);

            return response()->json([
                'message' => 'Sale completed successfully',
                'sale' => $sale,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create sale: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single sale
     */
    public function show(Request $request, Sale $sale): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        // Check access
        if ($sale->branch_id !== $branch?->id && ! $user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $sale->load(['items.product:id,name,sku,image', 'customer:id,name,phone', 'user:id,name', 'branch.tenant:id,name']);

        return response()->json([
            'sale' => $sale,
        ]);
    }

    /**
     * Cancel/void a sale
     */
    public function cancel(Request $request, Sale $sale): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        // Check access
        if ($sale->branch_id !== $branch?->id && ! $user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($sale->status !== Sale::STATUS_COMPLETED) {
            return response()->json([
                'message' => 'Only completed sales can be cancelled',
            ], 400);
        }

        // Managers/admins can cancel any sale; cashiers can only cancel their own
        if (! $user->isManager() && ! $user->isAdmin() && $sale->user_id !== $user->id) {
            return response()->json([
                'message' => 'You can only void your own sales',
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Restore stock
            foreach ($sale->items as $item) {
                $product = $item->product;

                if ($product && $product->track_stock) {
                    $stock = Stock::where('branch_id', $sale->branch_id)
                        ->where('product_id', $item->product_id)
                        ->where('variant_id', $item->variant_id)
                        ->first();

                    if ($stock) {
                        $stock->increment('quantity', $item->quantity);

                        // Record stock movement
                        StockMovement::create([
                            'branch_id' => $sale->branch_id,
                            'product_id' => $item->product_id,
                            'variant_id' => $item->variant_id,
                            'user_id' => $user->id,
                            'type' => 'sale_cancelled',
                            'quantity' => $item->quantity,
                            'balance_after' => $stock->quantity,
                            'reference' => "SALE-CANCEL-{$sale->id}",
                            'notes' => "Sale cancelled #{$sale->receipt_number}",
                        ]);
                    }
                }
            }

            // Update sale status
            $sale->update([
                'status' => Sale::STATUS_CANCELLED,
            ]);

            DB::commit();

            // Clear product cache to reflect updated stock levels
            cache()->forget("pos_all_data_{$user->tenant_id}_{$sale->branch_id}");
            cache()->forget("pos_products_{$user->tenant_id}_{$sale->branch_id}_all_none");

            return response()->json([
                'message' => 'Sale cancelled successfully',
                'sale' => $sale,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to cancel sale: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Attach a customer to a completed sale
     */
    public function attachCustomer(Request $request, Sale $sale): JsonResponse
    {
        $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
        ]);

        $user = $request->user();
        $branch = $user->primaryBranch();

        // Check access
        if ($sale->branch_id !== $branch?->id && ! $user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($sale->customer_id) {
            return response()->json([
                'message' => 'Sale already has a customer attached',
                'sale' => $sale->load('customer:id,first_name,last_name,phone,email'),
            ]);
        }

        $sale->update([
            'customer_id' => $request->customer_id,
        ]);

        $sale->load('customer:id,first_name,last_name,phone,email');

        return response()->json([
            'message' => 'Customer attached to sale successfully',
            'sale' => $sale,
        ]);
    }

    /**
     * Get today's sales summary
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
            ], 400);
        }

        $date = $request->input('date', now()->toDateString());

        $query = Sale::where('branch_id', $branch->id)
            ->whereDate('created_at', $date)
            ->where('status', Sale::STATUS_COMPLETED);

        // Cashiers only see their own
        if ($user->isCashier()) {
            $query->where('user_id', $user->id);
        }

        $stats = $query->selectRaw('
            COUNT(*) as total_sales,
            COALESCE(SUM(total_amount), 0) as total_revenue,
            COALESCE(SUM(discount_amount), 0) as total_discounts,
            COALESCE(SUM(tax_amount), 0) as total_tax
        ')->first();

        // Payment method breakdown
        $paymentBreakdown = Sale::where('branch_id', $branch->id)
            ->whereDate('created_at', $date)
            ->where('status', Sale::STATUS_COMPLETED)
            ->when($user->isCashier(), fn ($q) => $q->where('user_id', $user->id))
            ->selectRaw('payment_method, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total')
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'date' => $date,
            'stats' => [
                'total_sales' => (int) $stats->total_sales,
                'total_revenue' => (float) $stats->total_revenue,
                'total_discounts' => (float) $stats->total_discounts,
                'total_tax' => (float) $stats->total_tax,
            ],
            'payment_breakdown' => $paymentBreakdown,
        ]);
    }
}
