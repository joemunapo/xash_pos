<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\UnitConversion;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UnitOfMeasureController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $units = UnitOfMeasure::where('company_id', $companyId)
            ->withCount('conversionsFrom')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('abbreviation', 'like', "%{$search}%");
                });
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $conversions = UnitConversion::where('company_id', $companyId)
            ->with(['fromUnit:id,name,abbreviation', 'toUnit:id,name,abbreviation'])
            ->get();

        $categories = UnitOfMeasure::where('company_id', $companyId)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return Inertia::render('Admin/Units/Index', [
            'units' => $units,
            'conversions' => $conversions,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'abbreviation' => [
                'required',
                'string',
                'max:10',
                Rule::unique('unit_of_measures')->where('company_id', $user->company_id),
            ],
            'category' => ['nullable', 'string', 'max:50'],
            'is_base_unit' => ['boolean'],
            'allow_decimal' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['company_id'] = $user->company_id;
        $validated['is_active'] = true;

        $unit = UnitOfMeasure::create($validated);

        ActivityLog::log(
            ActivityLog::ACTION_CREATED,
            $user->company_id,
            $user->id,
            null,
            UnitOfMeasure::class,
            $unit->id,
            null,
            $unit->toArray()
        );

        // Get updated units list for product create/edit pages
        $units = UnitOfMeasure::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => ['value' => $u->abbreviation, 'label' => $u->name])
            ->toArray();

        return back()->with('success', 'Unit created successfully.')
            ->with('created_unit_id', $unit->id)
            ->with('created_unit_abbreviation', $unit->abbreviation)
            ->with('units', $units);
    }

    public function update(Request $request, UnitOfMeasure $unit)
    {
        $this->authorizeAccess($unit);
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'abbreviation' => [
                'required',
                'string',
                'max:10',
                Rule::unique('unit_of_measures')->where('company_id', $user->company_id)->ignore($unit->id),
            ],
            'category' => ['nullable', 'string', 'max:50'],
            'is_base_unit' => ['boolean'],
            'allow_decimal' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $oldValues = $unit->toArray();
        $unit->update($validated);

        ActivityLog::log(
            ActivityLog::ACTION_UPDATED,
            $user->company_id,
            $user->id,
            null,
            UnitOfMeasure::class,
            $unit->id,
            $oldValues,
            $unit->fresh()->toArray()
        );

        return back()->with('success', 'Unit updated successfully.');
    }

    public function destroy(Request $request, UnitOfMeasure $unit)
    {
        $this->authorizeAccess($unit);
        $user = $request->user();

        // Check if unit is in use by products
        // Note: You may want to add this relationship later
        // if ($unit->products()->exists()) {
        //     return back()->with('error', 'Cannot delete unit that is assigned to products.');
        // }

        // Delete related conversions
        UnitConversion::where('from_unit_id', $unit->id)
            ->orWhere('to_unit_id', $unit->id)
            ->delete();

        ActivityLog::log(
            ActivityLog::ACTION_DELETED,
            $user->company_id,
            $user->id,
            null,
            UnitOfMeasure::class,
            $unit->id,
            $unit->toArray(),
            null
        );

        $unit->delete();

        return back()->with('success', 'Unit deleted successfully.');
    }

    public function storeConversion(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'from_unit_id' => ['required', 'exists:unit_of_measures,id'],
            'to_unit_id' => ['required', 'exists:unit_of_measures,id', 'different:from_unit_id'],
            'conversion_factor' => ['required', 'numeric', 'gt:0'],
        ]);

        // Verify both units belong to the company
        $fromUnit = UnitOfMeasure::where('id', $validated['from_unit_id'])
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $toUnit = UnitOfMeasure::where('id', $validated['to_unit_id'])
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        // Check if conversion already exists
        $exists = UnitConversion::where('company_id', $user->company_id)
            ->where('from_unit_id', $validated['from_unit_id'])
            ->where('to_unit_id', $validated['to_unit_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'This conversion already exists.');
        }

        $validated['company_id'] = $user->company_id;
        $conversion = UnitConversion::create($validated);

        return back()->with('success', 'Conversion added successfully.');
    }

    public function destroyConversion(Request $request, UnitConversion $conversion)
    {
        if ($conversion->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $conversion->delete();

        return back()->with('success', 'Conversion removed successfully.');
    }

    protected function authorizeAccess(UnitOfMeasure $unit): void
    {
        if ($unit->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}
