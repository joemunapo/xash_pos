<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoyaltyController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Get customers with loyalty points
        $customers = Customer::where('tenant_id', $tenantId)
            ->where('loyalty_points', '>', 0)
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
            ->orderBy('loyalty_points', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Stats
        $stats = [
            'total_members' => Customer::where('tenant_id', $tenantId)->where('loyalty_points', '>', 0)->count(),
            'total_points' => Customer::where('tenant_id', $tenantId)->sum('loyalty_points'),
            'points_redeemed' => LoyaltyTransaction::whereHas('customer', fn ($q) => $q->where('tenant_id', $tenantId))
                ->where('type', 'redeem')
                ->sum('points') * -1,
            'active_this_month' => LoyaltyTransaction::whereHas('customer', fn ($q) => $q->where('tenant_id', $tenantId))
                ->whereMonth('created_at', now()->month)
                ->distinct('customer_id')
                ->count('customer_id'),
        ];

        // Tier distribution
        $tierDistribution = [
            'bronze' => Customer::where('tenant_id', $tenantId)->where('loyalty_tier', 'bronze')->count(),
            'silver' => Customer::where('tenant_id', $tenantId)->where('loyalty_tier', 'silver')->count(),
            'gold' => Customer::where('tenant_id', $tenantId)->where('loyalty_tier', 'gold')->count(),
            'platinum' => Customer::where('tenant_id', $tenantId)->where('loyalty_tier', 'platinum')->count(),
        ];

        return Inertia::render('Admin/Customers/Loyalty', [
            'customers' => $customers,
            'stats' => $stats,
            'tierDistribution' => $tierDistribution,
            'filters' => $request->only(['search', 'tier']),
        ]);
    }

    public function transactions(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $transactions = LoyaltyTransaction::whereHas('customer', fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['customer', 'branch'])
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->customer_id, function ($query, $customerId) {
                $query->where('customer_id', $customerId);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::where('tenant_id', $tenantId)
            ->where('loyalty_points', '>', 0)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return Inertia::render('Admin/Customers/LoyaltyTransactions', [
            'transactions' => $transactions,
            'customers' => $customers,
            'filters' => $request->only(['type', 'customer_id', 'date_from', 'date_to']),
        ]);
    }

    public function adjustPoints(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'points' => ['required', 'numeric'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $customer = Customer::where('tenant_id', $user->tenant_id)
            ->findOrFail($validated['customer_id']);

        $customer->loyalty_points += $validated['points'];
        $customer->save();

        LoyaltyTransaction::create([
            'customer_id' => $customer->id,
            'branch_id' => null,
            'type' => 'adjust',
            'points' => $validated['points'],
            'balance_after' => $customer->loyalty_points,
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Loyalty points adjusted successfully.');
    }
}
