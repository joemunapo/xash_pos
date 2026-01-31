<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $suppliers = Supplier::where('company_id', $companyId)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->withCount('products')
            ->latest()
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        return Inertia::render('Admin/Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Suppliers/Create');
    }

    /**
     * Store a newly created supplier.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'credit_days' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $supplier = Supplier::create([
            ...$validated,
            'company_id' => $user->company_id,
        ]);

        // Log activity
        ActivityLog::log(
            ActivityLog::ACTION_CREATED,
            $user->company_id,
            $user->id,
            null,
            Supplier::class,
            $supplier->id,
            null,
            $supplier->only(['name', 'contact_person', 'email', 'phone'])
        );

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier): Response
    {
        $this->authorizeSupplier($supplier);

        $supplier->load(['products' => function ($query) {
            $query->select('products.id', 'products.name', 'products.sku', 'products.selling_price')
                ->withPivot(['supplier_sku', 'cost_price', 'is_primary']);
        }]);

        return Inertia::render('Admin/Suppliers/Show', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier): Response
    {
        $this->authorizeSupplier($supplier);

        return Inertia::render('Admin/Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Update the specified supplier.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $this->authorizeSupplier($supplier);
        $user = $request->user();

        $oldValues = $supplier->only(['name', 'contact_person', 'email', 'phone', 'is_active']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'credit_days' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $supplier->update($validated);

        // Log activity
        ActivityLog::log(
            ActivityLog::ACTION_UPDATED,
            $user->company_id,
            $user->id,
            null,
            Supplier::class,
            $supplier->id,
            $oldValues,
            $supplier->only(['name', 'contact_person', 'email', 'phone', 'is_active'])
        );

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(Supplier $supplier)
    {
        $this->authorizeSupplier($supplier);
        $user = auth()->user();

        // Log activity before deletion
        ActivityLog::log(
            ActivityLog::ACTION_DELETED,
            $user->company_id,
            $user->id,
            null,
            Supplier::class,
            $supplier->id,
            $supplier->only(['name', 'contact_person', 'email', 'phone']),
            null
        );

        $supplier->delete();

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Authorize access to supplier
     */
    private function authorizeSupplier(Supplier $supplier)
    {
        $user = auth()->user();

        if ($supplier->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to supplier.');
        }
    }
}
