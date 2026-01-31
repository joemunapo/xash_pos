<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $products = Product::where('company_id', $companyId)
            ->with('category:id,name')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('plu_code', 'like', "%{$search}%");
                });
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        $categories = Category::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'status', 'sort_by', 'sort_order']),
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();
        $categories = Category::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->get(['id', 'name', 'parent_id']);

        $parentCategories = Category::where('company_id', $user->company_id)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Admin/Products/Create', [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
            'units' => $this->getUnits(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('products')->where('company_id', $user->company_id)],
            'barcode' => ['nullable', 'string', 'max:50'],
            'plu_code' => ['nullable', 'string', 'max:10'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:10'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_taxable' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'track_stock' => ['boolean'],
            'track_expiry' => ['boolean'],
            'track_batches' => ['boolean'],
            'allow_decimal_qty' => ['boolean'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'reorder_quantity' => ['nullable', 'integer', 'min:0'],
            'packaging' => ['nullable', 'array'],
            'packaging.*.name' => ['required_with:packaging', 'string', 'max:50'],
            'packaging.*.abbreviation' => ['nullable', 'string', 'max:10'],
            'packaging.*.quantity' => ['required_with:packaging', 'integer', 'min:1'],
            'packaging.*.selling_price' => ['nullable', 'numeric', 'min:0'],
            'packaging.*.cost_price' => ['nullable', 'numeric', 'min:0'],
            'packaging.*.barcode' => ['nullable', 'string', 'max:50'],
        ]);

        $packaging = $validated['packaging'] ?? [];
        unset($validated['packaging']);

        $validated['company_id'] = $user->company_id;
        $validated['is_active'] = true;

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Generate SKU if not provided
        if (empty($validated['sku'])) {
            $validated['sku'] = 'PRD-'.strtoupper(Str::random(8));
        }

        $product = Product::create($validated);

        // Create product packaging units
        foreach ($packaging as $index => $pkg) {
            if (! empty($pkg['name']) && ! empty($pkg['quantity'])) {
                ProductUnit::create([
                    'product_id' => $product->id,
                    'name' => $pkg['name'],
                    'abbreviation' => $pkg['abbreviation'] ?? strtolower(substr($pkg['name'], 0, 3)),
                    'quantity' => $pkg['quantity'],
                    'selling_price' => $pkg['selling_price'] ?: null,
                    'cost_price' => $pkg['cost_price'] ?: null,
                    'barcode' => $pkg['barcode'] ?: null,
                    'sort_order' => $index,
                ]);
            }
        }

        ActivityLog::log(
            ActivityLog::ACTION_CREATED,
            $user->company_id,
            $user->id,
            null,
            Product::class,
            $product->id,
            null,
            $product->only(['id', 'name', 'sku', 'selling_price'])
        );

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): Response
    {
        $this->authorizeAccess($product);

        $product->load(['category', 'variants', 'suppliers']);

        return Inertia::render('Admin/Products/Show', [
            'product' => $product,
        ]);
    }

    public function edit(Request $request, Product $product): Response
    {
        $this->authorizeAccess($product);

        $user = $request->user();
        $categories = Category::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->get(['id', 'name', 'parent_id']);

        $parentCategories = Category::where('company_id', $user->company_id)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->get(['id', 'name']);

        $product->load('productUnits');

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => $categories,
            'parentCategories' => $parentCategories,
            'units' => $this->getUnits(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeAccess($product);
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('products')->where('company_id', $user->company_id)->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:50'],
            'plu_code' => ['nullable', 'string', 'max:10'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:10'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_taxable' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'track_stock' => ['boolean'],
            'track_expiry' => ['boolean'],
            'track_batches' => ['boolean'],
            'allow_decimal_qty' => ['boolean'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'reorder_quantity' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'packaging' => ['nullable', 'array'],
            'packaging.*.name' => ['required_with:packaging', 'string', 'max:50'],
            'packaging.*.abbreviation' => ['nullable', 'string', 'max:10'],
            'packaging.*.quantity' => ['required_with:packaging', 'integer', 'min:1'],
            'packaging.*.selling_price' => ['nullable', 'numeric', 'min:0'],
            'packaging.*.cost_price' => ['nullable', 'numeric', 'min:0'],
            'packaging.*.barcode' => ['nullable', 'string', 'max:50'],
        ]);

        $packaging = $validated['packaging'] ?? [];
        unset($validated['packaging']);

        $oldValues = $product->only(['name', 'sku', 'selling_price', 'is_active']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        // Update product packaging units
        // Delete existing packaging units
        $product->productUnits()->delete();

        // Create new packaging units
        foreach ($packaging as $index => $pkg) {
            if (! empty($pkg['name']) && ! empty($pkg['quantity'])) {
                ProductUnit::create([
                    'product_id' => $product->id,
                    'name' => $pkg['name'],
                    'abbreviation' => $pkg['abbreviation'] ?? strtolower(substr($pkg['name'], 0, 3)),
                    'quantity' => $pkg['quantity'],
                    'selling_price' => $pkg['selling_price'] ?: null,
                    'cost_price' => $pkg['cost_price'] ?: null,
                    'barcode' => $pkg['barcode'] ?: null,
                    'sort_order' => $index,
                ]);
            }
        }

        ActivityLog::log(
            ActivityLog::ACTION_UPDATED,
            $user->company_id,
            $user->id,
            null,
            Product::class,
            $product->id,
            $oldValues,
            $product->only(['name', 'sku', 'selling_price', 'is_active'])
        );

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorizeAccess($product);
        $user = $request->user();

        // Soft delete
        ActivityLog::log(
            ActivityLog::ACTION_DELETED,
            $user->company_id,
            $user->id,
            null,
            Product::class,
            $product->id,
            $product->only(['id', 'name', 'sku']),
            null
        );

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    protected function authorizeAccess(Product $product): void
    {
        if ($product->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }

    protected function getUnits(): array
    {
        $user = auth()->user();

        $units = UnitOfMeasure::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['abbreviation as value', 'name as label'])
            ->toArray();

        // Return defaults if no custom units exist
        if (empty($units)) {
            return [
                ['value' => 'pcs', 'label' => 'Pieces'],
                ['value' => 'kg', 'label' => 'Kilograms'],
                ['value' => 'g', 'label' => 'Grams'],
                ['value' => 'l', 'label' => 'Liters'],
                ['value' => 'ml', 'label' => 'Milliliters'],
                ['value' => 'box', 'label' => 'Box'],
                ['value' => 'pack', 'label' => 'Pack'],
                ['value' => 'dozen', 'label' => 'Dozen'],
                ['value' => 'crate', 'label' => 'Crate'],
            ];
        }

        return $units;
    }
}
