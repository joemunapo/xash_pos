<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products for the branch
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
                'data' => [],
            ]);
        }

        $products = Product::where('company_id', $user->company_id)
            ->with(['stock' => function ($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            }, 'category:id,name'])
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('sku', 'like', "%{$request->search}%")
                        ->orWhere('barcode', 'like', "%{$request->search}%");
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('is_active', $request->status === 'active');
            })
            ->when($request->category, function ($query) use ($request) {
                $query->where('category_id', $request->category);
            })
            ->select([
                'id', 'name', 'sku', 'image', 'selling_price', 'cost_price',
                'reorder_level', 'is_active', 'category_id', 'created_at',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($product) {
                $stock = $product->stock->first();

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'image' => $product->image,
                    'price' => $product->selling_price,
                    'cost_price' => $product->cost_price,
                    'stock_quantity' => (int) ($stock?->quantity ?? 0),
                    'reorder_level' => $product->reorder_level,
                    'is_active' => $product->is_active,
                    'category' => $product->category,
                    'created_at' => $product->created_at,
                ];
            });

        // Filter by stock level after mapping
        if ($request->stock === 'low') {
            $products = $products->filter(function ($product) {
                return $product['stock_quantity'] <= ($product['reorder_level'] ?? 10);
            })->values();
        } elseif ($request->stock === 'high') {
            $products = $products->filter(function ($product) {
                return $product['stock_quantity'] > ($product['reorder_level'] ?? 10);
            })->values();
        }

        return response()->json(['data' => $products]);
    }

    /**
     * Get a single product with stats
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        // Ensure product belongs to the user's company
        if ($product->company_id !== $user->company_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Load relationships
        $product->load('category:id,name');

        // Get stock for this branch
        $stock = Stock::where('branch_id', $branch?->id)
            ->where('product_id', $product->id)
            ->first();

        // Determine date range based on period
        $period = $request->input('period', '30');
        $startDate = match ($period) {
            '7' => Carbon::now()->subDays(7),
            '30' => Carbon::now()->subDays(30),
            '90' => Carbon::now()->subDays(90),
            default => null, // all time
        };

        // Build sales query
        $salesQuery = SaleItem::where('product_id', $product->id)
            ->whereHas('sale', function ($query) use ($branch, $startDate) {
                $query->where('branch_id', $branch?->id)
                    ->where('status', Sale::STATUS_COMPLETED);

                if ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                }
            });

        // Calculate stats
        $stats = [
            'current_stock' => $stock?->quantity ?? 0,
            'total_sold' => (clone $salesQuery)->sum('quantity'),
            'total_revenue' => (clone $salesQuery)->sum('line_total'),
            'total_profit' => (clone $salesQuery)->selectRaw('SUM(line_total - (cost_price * quantity)) as profit')->value('profit') ?? 0,
        ];

        // Get recent sales
        $recentSales = SaleItem::where('product_id', $product->id)
            ->whereHas('sale', function ($query) use ($branch, $startDate) {
                $query->where('branch_id', $branch?->id)
                    ->where('status', Sale::STATUS_COMPLETED);

                if ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                }
            })
            ->with(['sale:id,receipt_number,user_id,created_at', 'sale.user:id,name'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'receipt_number' => $item->sale->receipt_number,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                    'cashier_name' => $item->sale->user?->name ?? 'Unknown',
                    'created_at' => $item->sale->created_at,
                ];
            });

        // Check if there are more sales
        $totalSalesCount = SaleItem::where('product_id', $product->id)
            ->whereHas('sale', function ($query) use ($branch, $startDate) {
                $query->where('branch_id', $branch?->id)
                    ->where('status', Sale::STATUS_COMPLETED);

                if ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                }
            })
            ->count();

        return response()->json([
            'product' => $product,
            'stats' => $stats,
            'recent_sales' => $recentSales,
            'has_more_sales' => $totalSalesCount > 10,
        ]);
    }

    /**
     * Get paginated sales for a product
     */
    public function sales(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        // Ensure product belongs to the user's company
        if ($product->company_id !== $user->company_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $period = $request->input('period', '30');
        $page = $request->input('page', 1);
        $perPage = 10;

        $startDate = match ($period) {
            '7' => Carbon::now()->subDays(7),
            '30' => Carbon::now()->subDays(30),
            '90' => Carbon::now()->subDays(90),
            default => null,
        };

        $sales = SaleItem::where('product_id', $product->id)
            ->whereHas('sale', function ($query) use ($branch, $startDate) {
                $query->where('branch_id', $branch?->id)
                    ->where('status', Sale::STATUS_COMPLETED);

                if ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                }
            })
            ->with(['sale:id,receipt_number,user_id,created_at', 'sale.user:id,name'])
            ->orderByDesc('created_at')
            ->skip(($page - 1) * $perPage)
            ->take($perPage + 1)
            ->get();

        $hasMore = $sales->count() > $perPage;
        $sales = $sales->take($perPage)->map(function ($item) {
            return [
                'id' => $item->id,
                'receipt_number' => $item->sale->receipt_number,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
                'cashier_name' => $item->sale->user?->name ?? 'Unknown',
                'created_at' => $item->sale->created_at,
            ];
        });

        return response()->json([
            'sales' => $sales,
            'has_more' => $hasMore,
        ]);
    }

    /**
     * Create a new product
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku',
            'barcode' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'nullable|string|max:20',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'track_stock' => 'boolean',
            'track_expiry' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $stockQuantity = $validated['stock_quantity'];
        unset($validated['stock_quantity']);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Auto-generate SKU if not provided
        if (empty($validated['sku'])) {
            $validated['sku'] = 'PRD-'.strtoupper(substr(md5(uniqid()), 0, 8));
        }

        $product = Product::create(array_merge($validated, [
            'company_id' => $user->company_id,
            'is_active' => true,
        ]));

        // Create stock record for the branch
        Stock::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => $stockQuantity,
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }

    /**
     * Update a product
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        // Ensure product belongs to the user's company
        if ($product->company_id !== $user->company_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|unique:products,sku,'.$product->id,
            'barcode' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'nullable|string|max:20',
            'selling_price' => 'sometimes|numeric|min:0',
            'cost_price' => 'sometimes|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'track_stock' => 'sometimes|boolean',
            'track_expiry' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && \Storage::disk('public')->exists($product->image)) {
                \Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ]);
    }

    /**
     * Delete a product
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        // Ensure product belongs to the user's company
        if ($product->company_id !== $user->company_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Get categories for the company
     */
    public function categories(Request $request): JsonResponse
    {
        $user = $request->user();

        $categories = Category::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        return response()->json(['data' => $categories]);
    }
}
