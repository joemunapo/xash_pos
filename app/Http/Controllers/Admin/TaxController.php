<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Tax;
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
        $companyId = $user->company_id;

        $taxes = Tax::where('company_id', $companyId)
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->status !== null, function ($query) use ($request) {
                $query->where('is_active', $request->status === 'active');
            })
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Settings/Tax', [
            'taxes' => $taxes,
            'filters' => $request->only(['search', 'status']),
        ]);
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

        $validated['company_id'] = $user->company_id;

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            Tax::where('company_id', $user->company_id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $tax = Tax::create($validated);

        ActivityLog::log(
            ActivityLog::ACTION_CREATED,
            'Tax',
            $tax->id,
            "Created tax rate: {$tax->name} ({$tax->rate}%)",
            $user->id
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
        if (($validated['is_default'] ?? false) && !$tax->is_default) {
            Tax::where('company_id', $user->company_id)
                ->where('id', '!=', $tax->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $tax->update($validated);

        ActivityLog::log(
            ActivityLog::ACTION_UPDATED,
            'Tax',
            $tax->id,
            "Updated tax rate: {$tax->name} ({$tax->rate}%)",
            $user->id
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

        $taxName = $tax->name;
        $tax->delete();

        ActivityLog::log(
            ActivityLog::ACTION_DELETED,
            'Tax',
            $tax->id,
            "Deleted tax rate: {$taxName}",
            $user->id
        );

        return redirect()->back()->with('success', 'Tax rate deleted successfully.');
    }

    /**
     * Authorize tax access
     */
    private function authorizeTax(Tax $tax)
    {
        $user = auth()->user();

        if ($tax->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to tax.');
        }
    }
}
