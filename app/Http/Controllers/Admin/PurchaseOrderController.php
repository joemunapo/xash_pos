<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;

        // For now, return empty data with structure
        // Once migrations are complete, this will query actual data
        $purchaseOrders = [
            'data' => [],
            'links' => [],
            'from' => 0,
            'to' => 0,
            'total' => 0,
        ];

        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        $branches = Branch::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Admin/PurchaseOrders/Index', [
            'purchaseOrders' => $purchaseOrders,
            'suppliers' => $suppliers,
            'branches' => $branches,
            'filters' => $request->only(['search', 'status', 'supplier_id', 'branch_id']),
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'name', 'payment_terms', 'credit_days']);

        $branches = Branch::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        $products = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->with('suppliers:id,name')
            ->get(['id', 'name', 'sku', 'cost_price', 'selling_price']);

        return Inertia::render('Admin/PurchaseOrders/Create', [
            'suppliers' => $suppliers,
            'branches' => $branches,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        // Validation for when database is ready
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery' => ['nullable', 'date', 'after_or_equal:order_date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // For now, just return success message
        // Once migrations complete, this will create actual PO
        return redirect()->route('admin.purchase-orders.index')
            ->with('success', 'Purchase Order will be created once database setup is complete.');
    }

    public function show(int $id): Response
    {
        // Mock data for demonstration
        $purchaseOrder = (object) [
            'id' => $id,
            'po_number' => 'PO-202512-0001',
            'status' => 'pending',
            'order_date' => now()->format('Y-m-d'),
            'expected_delivery' => now()->addDays(7)->format('Y-m-d'),
            'subtotal' => 1000.00,
            'tax_amount' => 150.00,
            'discount_amount' => 0,
            'total_amount' => 1150.00,
            'notes' => 'Sample purchase order',
            'supplier' => (object) ['id' => 1, 'name' => 'Sample Supplier'],
            'branch' => (object) ['id' => 1, 'name' => 'Main Branch'],
            'user' => (object) ['id' => 1, 'name' => 'Admin User'],
            'items' => [],
        ];

        return Inertia::render('Admin/PurchaseOrders/Show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }
}
