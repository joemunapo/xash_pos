<?php

namespace App\Http\Controllers\SuperAdmin\Web;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SubscriptionPlanController extends Controller
{
    public function index(): \Inertia\Response
    {
        $plans = SubscriptionPlan::withCount([
            'subscriptions as active_subscriptions_count' => function ($query) {
                $query->where('status', 'active');
            },
        ])->orderBy('sort_order')->get();

        return Inertia::render('SuperAdmin/Plans/Index', [
            'plans' => $plans,
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('SuperAdmin/Plans/Create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'max_users' => 'required|integer|min:1',
            'max_branches' => 'required|integer|min:1',
            'max_products' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        SubscriptionPlan::create($validated);

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function edit(SubscriptionPlan $plan): \Inertia\Response
    {
        return Inertia::render('SuperAdmin/Plans/Edit', [
            'plan' => $plan,
        ]);
    }

    public function update(Request $request, SubscriptionPlan $plan): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price_monthly' => 'sometimes|required|numeric|min:0',
            'price_yearly' => 'sometimes|required|numeric|min:0',
            'max_users' => 'sometimes|required|integer|min:1',
            'max_branches' => 'sometimes|required|integer|min:1',
            'max_products' => 'sometimes|required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $plan->update($validated);

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(SubscriptionPlan $plan): \Illuminate\Http\RedirectResponse
    {
        $plan->delete();

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
}
