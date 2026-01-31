<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with(['tenant', 'subscriptionPlan']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->tenant_id) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->expiring_soon) {
            $query->expiringSoon($request->get('days', 7));
        }

        $subscriptions = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($subscriptions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);
        $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);

        $duration = $validated['billing_cycle'] === 'yearly' ? 365 : 30;

        $subscription = Subscription::create([
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

        // Update tenant status
        $tenant->update(['subscription_status' => 'active']);

        return response()->json([
            'message' => 'Subscription created successfully',
            'subscription' => $subscription->load('tenant', 'subscriptionPlan'),
        ], 201);
    }

    public function show(Subscription $subscription)
    {
        return response()->json($subscription->load('tenant', 'subscriptionPlan'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'subscription_plan_id' => 'sometimes|exists:subscription_plans,id',
            'billing_cycle' => 'sometimes|in:monthly,yearly',
            'status' => 'sometimes|in:active,cancelled,expired',
        ]);

        if (isset($validated['subscription_plan_id'])) {
            $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);
            $billingCycle = $validated['billing_cycle'] ?? $subscription->billing_cycle;
            $subscription->upgradeToPlan($plan, $billingCycle);
        } else {
            $subscription->update($validated);
        }

        return response()->json([
            'message' => 'Subscription updated successfully',
            'subscription' => $subscription->fresh(),
        ]);
    }

    public function renew(Subscription $subscription)
    {
        $subscription->renew();

        return response()->json([
            'message' => 'Subscription renewed successfully',
            'subscription' => $subscription->fresh(),
        ]);
    }

    public function cancel(Subscription $subscription)
    {
        $subscription->cancel();

        return response()->json([
            'message' => 'Subscription cancelled successfully',
            'subscription' => $subscription->fresh(),
        ]);
    }
}
