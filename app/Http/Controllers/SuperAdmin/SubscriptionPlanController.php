<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = SubscriptionPlan::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $plans = $query->orderBy('sort_order')->get();

        return response()->json($plans);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:subscription_plans,slug',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'max_users' => 'required|integer|min:1',
            'max_branches' => 'required|integer|min:1',
            'max_products' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $plan = SubscriptionPlan::create($validated);

        return response()->json([
            'message' => 'Subscription plan created successfully',
            'plan' => $plan,
        ], 201);
    }

    public function show(SubscriptionPlan $subscriptionPlan)
    {
        return response()->json($subscriptionPlan);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:subscription_plans,slug,' . $subscriptionPlan->id,
            'description' => 'nullable|string',
            'price_monthly' => 'sometimes|required|numeric|min:0',
            'price_yearly' => 'sometimes|required|numeric|min:0',
            'max_users' => 'sometimes|required|integer|min:1',
            'max_branches' => 'sometimes|required|integer|min:1',
            'max_products' => 'sometimes|required|integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $subscriptionPlan->update($validated);

        return response()->json([
            'message' => 'Subscription plan updated successfully',
            'plan' => $subscriptionPlan->fresh(),
        ]);
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->delete();

        return response()->json([
            'message' => 'Subscription plan deleted successfully',
        ]);
    }
}
