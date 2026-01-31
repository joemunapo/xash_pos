<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants
     */
    public function index(Request $request)
    {
        $query = Tenant::query()->withCount(['users', 'branches', 'products']);

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('subscription_status', $request->status);
        }

        // Filter by active
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $tenants = $query->paginate($perPage);

        return response()->json($tenants);
    }

    /**
     * Store a newly created tenant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tenants,slug',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'trading_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'registration_number' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'default_currency' => 'nullable|string|size:3',
            'fiscal_year_start' => 'nullable|integer|min:1|max:12',
            'subscription_plan_id' => 'nullable|exists:subscription_plans,id',
           'billing_cycle' => 'nullable|in:monthly,yearly',
            
            // Admin user details
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_phone' => 'nullable|string|max:20|unique:users,phone_number',
            'admin_password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();
        try {
            // Create tenant
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'] ?? Str::slug($validated['name']),
                'domain' => $validated['domain'] ?? null,
                'trading_name' => $validated['trading_name'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'registration_number' => $validated['registration_number'] ?? null,
                'vat_number' => $validated['vat_number'] ?? null,
                'website' => $validated['website'] ?? null,
                'default_currency' => $validated['default_currency'] ?? 'USD',
                'fiscal_year_start' => $validated['fiscal_year_start'] ?? 1,
                'is_active' => true,
                'subscription_status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
            ]);

            // Create subscription if plan provided
            if (isset($validated['subscription_plan_id'])) {
                $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);
                $billingCycle = $validated['billing_cycle'] ?? 'monthly';
                $duration = $billingCycle === 'yearly' ? 365 : 30;

                Subscription::create([
                    'tenant_id' => $tenant->id,
                    'subscription_plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'plan_slug' => $plan->slug,
                    'price' => $plan->getPrice($billingCycle),
                    'billing_cycle' => $billingCycle,
                    'max_users' => $plan->max_users,
                    'max_branches' => $plan->max_branches,
                    'max_products' => $plan->max_products,
                    'features' => $plan->features,
                    'starts_at' => now(),
                    'ends_at' => now()->addDays($duration),
                    'renews_at' => now()->addDays($duration),
                    'status' => 'active',
                ]);

                // Update tenant status
                $tenant->update(['subscription_status' => 'active']);
            }

            // Create admin user
            $adminUser = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'phone_number' => $validated['admin_phone'] ?? null,
                'password' => Hash::make($validated['admin_password']),
                'role' => User::ROLE_ADMIN,
                'is_super_admin' => false,
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Tenant created successfully',
                'tenant' => $tenant->load('activeSubscription'),
                'admin_user' => $adminUser,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create tenant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified tenant
     */
    public function show(Tenant $tenant)
    {
        $tenant->load([
            'activeSubscription.subscriptionPlan',
            'users' => function ($query) {
                $query->withCount('branches')->latest();
            },
            'branches' => function ($query) {
                $query->withCount('users')->latest();
            },
        ]);

        $tenant->loadCount(['products', 'customers', 'suppliers']);

        // Get statistics
        $stats = [
            'total_users' => $tenant->users()->count(),
            'total_branches' => $tenant->branches()->count(),
            'total_products' => $tenant->products()->count(),
            'total_customers' => $tenant->customers()->count(),
            'total_sales' => 0, // You can add sales count if needed
        ];

        return response()->json([
            'tenant' => $tenant,
            'stats' => $stats,
        ]);
    }

    /**
     * Update the specified tenant
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('tenants')->ignore($tenant->id)],
            'domain' => ['nullable', 'string', 'max:255', Rule::unique('tenants')->ignore($tenant->id)],
            'trading_name' => 'nullable|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'registration_number' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'default_currency' => 'nullable|string|size:3',
            'fiscal_year_start' => 'nullable|integer|min:1|max:12',
            'is_active' => 'sometimes|boolean',
        ]);

        $tenant->update($validated);

        return response()->json([
            'message' => 'Tenant updated successfully',
            'tenant' => $tenant->fresh(),
        ]);
    }

    /**
     * Remove the specified tenant
     */
    public function destroy(Tenant $tenant)
    {
        try {
            $tenant->delete();

            return response()->json([
                'message' => 'Tenant deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete tenant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Suspend a tenant
     */
    public function suspend(Tenant $tenant)
    {
        $tenant->suspend();

        return response()->json([
            'message' => 'Tenant suspended successfully',
            'tenant' => $tenant->fresh(),
        ]);
    }

    /**
     * Activate a tenant
     */
    public function activate(Tenant $tenant)
    {
        $tenant->activate();

        return response()->json([
            'message' => 'Tenant activated successfully',
            'tenant' => $tenant->fresh(),
        ]);
    }
}
