<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Get inventory for the branch
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
                'items' => [],
            ]);
        }

        $items = Stock::where('branch_id', $branch->id)
            ->with('product:id,name,sku,cost_price,reorder_level,unit,image')
            ->whereHas('product') // Only get stocks that have a product
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('sku', 'like', "%{$request->search}%");
                });
            })
            ->when($request->stock_status, function ($query) use ($request) {
                if ($request->stock_status === 'low') {
                    $query->whereHas('product', function ($q) {
                        $q->whereRaw('stock.quantity <= products.reorder_level');
                    });
                } elseif ($request->stock_status === 'out') {
                    $query->where('quantity', 0);
                } elseif ($request->stock_status === 'good') {
                    $query->whereHas('product', function ($q) {
                        $q->whereRaw('stock.quantity > products.reorder_level');
                    });
                }
            })
            ->orderByDesc('created_at')
            ->get()
            ->filter(function ($stock) {
                // Additional safety: filter out any stocks without products
                return $stock->product !== null;
            })
            ->map(function ($stock) {
                $product = $stock->product;

                // Extra safety check
                if (! $product) {
                    return null;
                }

                return [
                    'id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $stock->quantity,
                    'unit' => $product->unit ?? 'pcs',
                    'reorder_level' => $product->reorder_level,
                    'stock_value' => $stock->quantity * $product->cost_price,
                    'image_url' => $product->image ? asset('storage/' . $product->image) : null,
                ];
            })
            ->filter() // Remove any null values from the map
            ->values(); // Re-index the array after filtering

        // Calculate summary
        $summary = [
            'total_products' => Product::where('company_id', $user->company_id)->count(),
            'total_value' => Stock::where('branch_id', $branch->id)
                ->join('products', 'stock.product_id', '=', 'products.id')
                ->selectRaw('SUM(stock.quantity * products.cost_price) as total')
                ->value('total') ?? 0,
            'low_stock_count' => Stock::where('branch_id', $branch->id)
                ->join('products', 'stock.product_id', '=', 'products.id')
                ->whereRaw('stock.quantity <= products.reorder_level')
                ->count(),
            'out_of_stock_count' => Stock::where('branch_id', $branch->id)
                ->where('quantity', 0)
                ->count(),
        ];

        return response()->json([
            'items' => $items,
            'summary' => $summary,
        ]);
    }

    /**
     * Adjust inventory for a product
     */
    public function adjust(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        // Ensure product belongs to the user's company
        if ($product->company_id !== $user->company_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
            ], 403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer',
            'type' => 'required|in:add,remove,set',
            'reason' => 'nullable|string|max:255',
        ]);

        // Find or create stock record for this product in the branch
        $stock = Stock::where('branch_id', $branch->id)
            ->where('product_id', $product->id)
            ->first();

        if (! $stock) {
            return response()->json([
                'message' => 'Stock record not found for this product in your branch',
            ], 404);
        }

        $oldQuantity = $stock->quantity;
        $quantity = $validated['quantity'];

        // Adjust quantity based on type
        if ($validated['type'] === 'add') {
            $stock->quantity += $quantity;
        } elseif ($validated['type'] === 'remove') {
            $stock->quantity = max(0, $stock->quantity - $quantity);
        } else { // set
            $stock->quantity = max(0, $quantity);
        }

        $stock->save();

        // Log the adjustment (optional)
        // StockMovement::create([
        //     'product_id' => $product->id,
        //     'branch_id' => $branch->id,
        //     'user_id' => $user->id,
        //     'type' => $validated['type'],
        //     'quantity' => $quantity,
        //     'old_quantity' => $oldQuantity,
        //     'new_quantity' => $stock->quantity,
        //     'reason' => $validated['reason'],
        // ]);

        return response()->json([
            'message' => 'Inventory adjusted successfully',
            'product_id' => $product->id,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $stock->quantity,
        ]);
    }

    /**
     * Get low stock items
     */
    public function lowStock(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
                'items' => [],
            ]);
        }

        $lowStockItems = Stock::where('branch_id', $branch->id)
            ->join('products', 'stock.product_id', '=', 'products.id')
            ->where('products.company_id', $user->company_id)
            ->whereRaw('stock.quantity <= products.reorder_level')
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                'stock.quantity',
                'products.reorder_level',
            ])
            ->orderBy('stock.quantity')
            ->get();

        return response()->json($lowStockItems);
    }
}
