<?php

namespace App\Http\Controllers\SuperAdmin\Web;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TenantController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $query = Tenant::query()
            ->with('activeSubscription')
            ->withCount(['users', 'branches', 'products']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('subscription_status', $request->status);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tenants = $query->paginate(15)->withQueryString();

        return Inertia::render('SuperAdmin/Tenants/Index', [
            'tenants' => $tenants,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): \Inertia\Response
    {
        $plans = SubscriptionPlan::active()->get();

        return Inertia::render('SuperAdmin/Tenants/Create', [
            'plans' => $plans,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trading_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'registration_number' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'default_currency' => 'nullable|string|size:3',
            'subscription_plan_id' => 'nullable|exists:subscription_plans,id',
            'billing_cycle' => 'nullable|in:monthly,yearly',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_phone' => 'nullable|string|max:20|unique:users,phone_number',
            'admin_password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();
        try {
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'trading_name' => $validated['trading_name'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'registration_number' => $validated['registration_number'] ?? null,
                'vat_number' => $validated['vat_number'] ?? null,
                'website' => $validated['website'] ?? null,
                'default_currency' => $validated['default_currency'] ?? 'USD',
                'is_active' => true,
                'subscription_status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
            ]);

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

                $tenant->update(['subscription_status' => 'active']);
            }

            User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'phone_number' => $validated['admin_phone'] ?? null,
                'password' => Hash::make($validated['admin_password']),
                'role' => User::ROLE_ADMIN,
                'is_super_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('superadmin.tenants.show', $tenant)
                ->with('success', 'Tenant created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to create tenant: '.$e->getMessage());
        }
    }

    public function show(Tenant $tenant): \Inertia\Response
    {
        $tenant->load([
            'activeSubscription.subscriptionPlan',
            'subscriptions' => function ($query) {
                $query->latest()->limit(10);
            },
            'users' => function ($query) {
                $query->latest();
            },
            'branches' => function ($query) {
                $query->latest();
            },
        ]);

        $tenant->loadCount(['users', 'branches', 'products', 'customers', 'suppliers']);

        $plans = SubscriptionPlan::active()->get();

        return Inertia::render('SuperAdmin/Tenants/Show', [
            'tenant' => $tenant,
            'plans' => $plans,
        ]);
    }

    public function update(Request $request, Tenant $tenant): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'trading_name' => 'nullable|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'registration_number' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'default_currency' => 'nullable|string|size:3',
            'is_active' => 'sometimes|boolean',
        ]);

        $tenant->update($validated);

        return back()->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant): \Illuminate\Http\RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('superadmin.tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }

    public function suspend(Tenant $tenant): \Illuminate\Http\RedirectResponse
    {
        $tenant->suspend();

        return back()->with('success', 'Tenant suspended successfully.');
    }

    public function activate(Tenant $tenant): \Illuminate\Http\RedirectResponse
    {
        $tenant->activate();

        return back()->with('success', 'Tenant activated successfully.');
    }

    public function assignSubscription(Request $request, Tenant $tenant): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);
        $duration = $validated['billing_cycle'] === 'yearly' ? 365 : 30;

        Subscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'plan_slug' => $plan->slug,
            'price' => $plan->getPrice($validated['billing_cycle']),
            'billing_cycle' => $validated['billing_cycle'],
            'max_users' => $plan->max_users,
            'max_branches' => $plan->max_branches,
            'max_products' => $plan->max_products,
            'features' => $plan->features,
            'starts_at' => now(),
            'ends_at' => now()->addDays($duration),
            'renews_at' => now()->addDays($duration),
            'status' => 'active',
        ]);

        $tenant->update(['subscription_status' => 'active']);

        return back()->with('success', 'Subscription assigned successfully.');
    }
}
