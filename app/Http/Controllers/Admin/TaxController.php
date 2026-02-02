<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Tax;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaxController extends Controller
{
    /**
     * Display a listing of taxes
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;
        $tenant = Tenant::find($tenantId);

        $taxes = Tax::where('tenant_id', $tenantId)
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->status !== null, function ($query) use ($request) {
                $query->where('is_active', $request->status === 'active');
            })
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $branches = Branch::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get(['id', 'name', 'settings']);

        // Get tax settings
        $taxEnabled = $tenant->settings['tax_enabled'] ?? false;
        $branchTaxSettings = [];
        foreach ($branches as $branch) {
            $branchTaxSettings[$branch->id] = $branch->settings['tax_enabled'] ?? true;
        }

        return Inertia::render('Admin/Settings/Tax', [
            'taxes' => $taxes,
            'branches' => $branches,
            'taxEnabled' => $taxEnabled,
            'branchTaxSettings' => $branchTaxSettings,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Toggle tax enabled for the tenant
     */
    public function toggleTax(Request $request)
    {
        $user = $request->user();
        $tenant = Tenant::find($user->tenant_id);

        $settings = $tenant->settings ?? [];
        $settings['tax_enabled'] = $request->boolean('enabled');
        $tenant->update(['settings' => $settings]);

        return back()->with('success', $settings['tax_enabled'] ? 'Tax enabled successfully.' : 'Tax disabled successfully.');
    }

    /**
     * Toggle tax enabled for a specific branch
     */
    public function toggleBranchTax(Request $request, Branch $branch)
    {
        if ($branch->tenant_id !== $request->user()->tenant_id) {
            abort(403);
        }

        $settings = $branch->settings ?? [];
        $settings['tax_enabled'] = $request->boolean('enabled');
        $branch->update(['settings' => $settings]);

        return back()->with('success', 'Branch tax setting updated.');
    }

    /**
     * Store a newly created tax
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['tenant_id'] = $user->tenant_id;

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            Tax::where('tenant_id', $user->tenant_id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $tax = Tax::create($validated);

        ActivityLog::log(
            ActivityLog::ACTION_CREATED,
            $user->tenant_id,
            $user->id,
            null,
            Tax::class,
            $tax->id,
            null,
            $tax->only(['id', 'name', 'rate', 'is_default'])
        );

        return redirect()->back()->with('success', 'Tax rate created successfully.');
    }

    /**
     * Update the specified tax
     */
    public function update(Request $request, Tax $tax)
    {
        $this->authorizeTax($tax);

        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        // If setting as default, unset other defaults
        if (($validated['is_default'] ?? false) && ! $tax->is_default) {
            Tax::where('tenant_id', $user->tenant_id)
                ->where('id', '!=', $tax->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $oldValues = $tax->only(['name', 'rate', 'is_default', 'is_active']);
        $tax->update($validated);

        ActivityLog::log(
            ActivityLog::ACTION_UPDATED,
            $user->tenant_id,
            $user->id,
            null,
            Tax::class,
            $tax->id,
            $oldValues,
            $tax->only(['name', 'rate', 'is_default', 'is_active'])
        );

        return redirect()->back()->with('success', 'Tax rate updated successfully.');
    }

    /**
     * Remove the specified tax
     */
    public function destroy(Tax $tax)
    {
        $this->authorizeTax($tax);

        $user = auth()->user();

        // Prevent deleting default tax
        if ($tax->is_default) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete the default tax rate.']);
        }

        $oldValues = $tax->only(['id', 'name', 'rate']);
        $tax->delete();

        ActivityLog::log(
            ActivityLog::ACTION_DELETED,
            $user->tenant_id,
            $user->id,
            null,
            Tax::class,
            $tax->id,
            $oldValues,
            null
        );

        return redirect()->back()->with('success', 'Tax rate deleted successfully.');
    }

    /**
     * Authorize tax access
     */
    private function authorizeTax(Tax $tax)
    {
        $user = auth()->user();

        if ($tax->tenant_id !== $user->tenant_id) {
            abort(403, 'Unauthorized access to tax.');
        }
    }
}
