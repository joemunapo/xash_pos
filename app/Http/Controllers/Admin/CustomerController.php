<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->tenant_id;

        $customers = Customer::where('tenant_id', $companyId)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->tier, function ($query, $tier) {
                $query->where('loyalty_tier', $tier);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search', 'tier', 'status', 'sort_by', 'sort_order']),
            'tiers' => [
                ['value' => 'bronze', 'label' => 'Bronze'],
                ['value' => 'silver', 'label' => 'Silver'],
                ['value' => 'gold', 'label' => 'Gold'],
                ['value' => 'platinum', 'label' => 'Platinum'],
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Customers/Create');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
        ]);

        try {
            $validated['tenant_id'] = $user->tenant_id;
            $validated['is_active'] = true;
            $validated['member_since'] = now();
            $validated['qr_code'] = 'CUS-'.strtoupper(Str::random(10));

            $customer = Customer::create($validated);

            ActivityLog::log(
                ActivityLog::ACTION_CREATED,
                $user->tenant_id,
                $user->id,
                null,
                Customer::class,
                $customer->id,
                null,
                $customer->only(['id', 'first_name', 'last_name', 'phone'])
            );

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create customer. Please try again.');
        }
    }

    public function show(Customer $customer): Response
    {
        $this->authorizeAccess($customer);

        $customer->load(['loyaltyTransactions' => function ($query) {
            $query->latest()->take(10);
        }]);

        return Inertia::render('Admin/Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer): Response
    {
        $this->authorizeAccess($customer);

        return Inertia::render('Admin/Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorizeAccess($customer);
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'is_active' => ['boolean'],
        ]);

        try {
            $oldValues = $customer->only(['first_name', 'last_name', 'phone', 'is_active']);
            $customer->update($validated);

            ActivityLog::log(
                ActivityLog::ACTION_UPDATED,
                $user->tenant_id,
                $user->id,
                null,
                Customer::class,
                $customer->id,
                $oldValues,
                $customer->only(['first_name', 'last_name', 'phone', 'is_active'])
            );

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update customer. Please try again.');
        }
    }

    public function destroy(Request $request, Customer $customer)
    {
        $this->authorizeAccess($customer);
        $user = $request->user();

        try {
            ActivityLog::log(
                ActivityLog::ACTION_DELETED,
                $user->tenant_id,
                $user->id,
                null,
                Customer::class,
                $customer->id,
                $customer->only(['id', 'first_name', 'last_name']),
                null
            );

            $customer->delete();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete customer. Please try again.');
        }
    }

    public function adjustPoints(Request $request, Customer $customer)
    {
        $this->authorizeAccess($customer);
        $user = $request->user();

        $validated = $request->validate([
            'points' => ['required', 'numeric'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $oldPoints = $customer->loyalty_points;
            $customer->loyalty_points += $validated['points'];
            $customer->save();

            $customer->loyaltyTransactions()->create([
                'branch_id' => null,
                'type' => 'adjust',
                'points' => $validated['points'],
                'balance_after' => $customer->loyalty_points,
                'description' => $validated['reason'],
            ]);

            ActivityLog::log(
                'loyalty_adjusted',
                $user->tenant_id,
                $user->id,
                null,
                Customer::class,
                $customer->id,
                ['points' => $oldPoints],
                ['points' => $customer->loyalty_points, 'reason' => $validated['reason']]
            );

            return back()->with('success', 'Loyalty points adjusted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to adjust loyalty points. Please try again.');
        }
    }

    protected function authorizeAccess(Customer $customer): void
    {
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
    }
}
