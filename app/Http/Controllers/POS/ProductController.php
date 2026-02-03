<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Get all categories for the user's company
     */
    public function categories(Request $request): JsonResponse
    {
        $user = $request->user();

        // Cache categories for 10 minutes
        $categories = cache()->remember("pos_categories_{$user->tenant_id}", 600, function () use ($user) {
            return Category::where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->with(['children' => function ($query) {
                    $query->where('is_active', true)
                        ->select('id', 'parent_id', 'name', 'slug', 'image', 'sort_order')
                        ->orderBy('sort_order')
                        ->orderBy('name');
                }])
                ->select('id', 'name', 'slug', 'image', 'parent_id', 'sort_order')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Get both categories and products in one request (optimized for mobile)
     */
    public function posData(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();
        $branchId = $branch?->id;

        // Cache categories
        $categories = cache()->remember("pos_categories_{$user->tenant_id}", 600, function () use ($user) {
            return Category::where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->select('id', 'name', 'slug')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });

        // Cache products
        $cacheKey = "pos_all_data_{$user->tenant_id}_{$branchId}";
        $products = cache()->remember($cacheKey, 300, function () use ($user, $branchId) {
            $query = Product::where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->with([
                    'category:id,name',
                    'productUnits:id,product_id,name,abbreviation,quantity,selling_price,cost_price,is_default,sort_order',
                ])
                ->select([
                    'id',
                    'category_id',
                    'name',
                    'barcode',
                    'unit',
                    'selling_price',
                    'cost_price',
                    'tax_rate',
                    'is_taxable',
                    'image',
                    'track_stock',
                    'allow_decimal_qty',
                ]);

            if ($branchId) {
                $query->with([
                    'stock' => function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                            ->select('product_id', 'quantity', 'reserved_quantity');
                    },
                    'branchPrices' => function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                            ->select('product_id', 'selling_price');
                    },
                ]);
            }

            $products = $query->orderBy('name')->get();

            if ($branchId) {
                $products->each(function ($product) {
                    $stock = $product->stock->first();
                    $product->stock_quantity = $stock ? (int) $stock->quantity : 0;

                    $branchPrice = $product->branchPrices->first();
                    $product->branch_price = $branchPrice ? $branchPrice->selling_price : $product->selling_price;

                    unset($product->stock, $product->branchPrices);
                });
            }

            return $products;
        });

        return response()->json([
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * Get products for POS
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();
        $branchId = $branch?->id;

        // Create cache key based on user, branch, and filters
        $cacheKey = sprintf(
            'pos_products_%d_%d_%s_%s',
            $user->tenant_id,
            $branchId ?? 0,
            $request->get('category_id', 'all'),
            $request->get('search', 'none')
        );

        // Cache for 5 minutes
        $products = cache()->remember($cacheKey, 300, function () use ($user, $branchId, $request) {
            $query = Product::where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->with([
                    'category:id,name,slug',
                    'productUnits:id,product_id,name,abbreviation,quantity,selling_price,cost_price,is_default,sort_order',
                ])
                ->select([
                    'id',
                    'category_id',
                    'name',
                    'sku',
                    'barcode',
                    'plu_code',
                    'unit',
                    'selling_price',
                    'cost_price',
                    'tax_rate',
                    'is_taxable',
                    'image',
                    'track_stock',
                    'allow_decimal_qty',
                ]);

            // Eager load stock and branch prices for the specific branch
            if ($branchId) {
                $query->with([
                    'stock' => function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                            ->select('id', 'product_id', 'branch_id', 'quantity', 'reserved_quantity');
                    },
                    'branchPrices' => function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                            ->select('id', 'product_id', 'branch_id', 'selling_price');
                    },
                ]);
            }

            // Filter by category
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Search by name, sku, barcode, or plu_code
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', $search)
                        ->orWhere('plu_code', $search);
                });
            }

            $products = $query->orderBy('name')->get();

            // Map the eager loaded relationships to attributes
            if ($branchId) {
                $products->each(function ($product) {
                    $stock = $product->stock->first();
                    $product->stock_quantity = $stock ? (int) $stock->quantity : 0;
                    $product->available_quantity = $stock ? (int) ($stock->quantity - $stock->reserved_quantity) : 0;

                    // Get branch-specific price if available
                    $branchPrice = $product->branchPrices->first();
                    $product->branch_price = $branchPrice ? $branchPrice->selling_price : $product->selling_price;

                    // Unset relationships to reduce response size
                    unset($product->stock, $product->branchPrices);
                });
            }

            return $products;
        });

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Get single product by ID, barcode, or PLU code
     */
    public function show(Request $request, string $identifier): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();
        $branchId = $branch?->id;

        $product = Product::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->where(function ($query) use ($identifier) {
                $query->where('id', $identifier)
                    ->orWhere('barcode', $identifier)
                    ->orWhere('plu_code', $identifier)
                    ->orWhere('sku', $identifier);
            })
            ->with(['category:id,name', 'productUnits'])
            ->first();

        if (! $product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        if ($branchId) {
            $stock = $product->getStockForBranch($branchId);
            $product->stock_quantity = $stock ? (int) $stock->quantity : 0;
            $product->available_quantity = $stock ? (int) $stock->available_quantity : 0;
            $product->branch_price = $product->getPriceForBranch($branchId);
        }

        return response()->json([
            'product' => $product,
        ]);
    }

    /**
     * Search products by barcode scan
     */
    public function scanBarcode(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => ['required', 'string'],
        ]);

        $user = $request->user();
        $branch = $user->primaryBranch();
        $branchId = $branch?->id;

        $product = Product::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->where(function ($query) use ($request) {
                $query->where('barcode', $request->barcode)
                    ->orWhere('plu_code', $request->barcode)
                    ->orWhere('sku', $request->barcode);
            })
            ->with(['category:id,name', 'productUnits'])
            ->first();

        if (! $product) {
            return response()->json([
                'message' => 'Product not found',
                'product' => null,
            ], 404);
        }

        if ($branchId) {
            $stock = $product->getStockForBranch($branchId);
            $product->stock_quantity = $stock ? (int) $stock->quantity : 0;
            $product->available_quantity = $stock ? (int) $stock->available_quantity : 0;
            $product->branch_price = $product->getPriceForBranch($branchId);
        }

        return response()->json([
            'product' => $product,
        ]);
    }

    /**
     * Get image as base64 for offline caching
     */
    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'path' => ['required', 'string'],
        ]);

        $path = $request->input('path');

        // Security: Ensure path doesn't escape storage directory
        $path = str_replace(['../', '..\\'], '', $path);

        // Check if file exists in storage
        if (! Storage::disk('public')->exists($path)) {
            return response()->json([
                'message' => 'Image not found',
                'base64' => null,
            ], 404);
        }

        $file = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path);
        $base64 = 'data:'.$mimeType.';base64,'.base64_encode($file);

        return response()->json([
            'base64' => $base64,
        ]);
    }

    /**
     * Get multiple images as base64 for batch offline caching
     */
    public function images(Request $request): JsonResponse
    {
        $request->validate([
            'paths' => ['required', 'array'],
            'paths.*' => ['required', 'string'],
        ]);

        $paths = $request->input('paths');
        $images = [];

        foreach ($paths as $path) {
            // Security: Ensure path doesn't escape storage directory
            $cleanPath = str_replace(['../', '..\\'], '', $path);

            if (Storage::disk('public')->exists($cleanPath)) {
                $file = Storage::disk('public')->get($cleanPath);
                $mimeType = Storage::disk('public')->mimeType($cleanPath);
                $images[$path] = 'data:'.$mimeType.';base64,'.base64_encode($file);
            } else {
                $images[$path] = null;
            }
        }

        return response()->json([
            'images' => $images,
        ]);
    }
}
