<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display super admin dashboard metrics
     */
    public function index(Request $request)
    {
        // Total tenants
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->count();
        $trialTenants = Tenant::where('subscription_status', 'trial')->count();
        $suspendedTenants = Tenant::where('subscription_status', 'suspended')->count();

        // Subscription metrics
        $activeSubscriptions = Subscription::active()->count();
        $expiringSoon = Subscription::expiringSoon(7)->count();
        $totalRevenue = Subscription::where('status', 'active')
            ->sum('price');

        // User metrics
        $totalUsers = User::where('is_super_admin', false)->count();

        // Growth metrics (last 30 days)
        $newTenantsLast30Days = Tenant::where('created_at', '>=', now()->subDays(30))->count();
        $newUsersLast30Days = User::where('created_at', '>=', now()->subDays(30))
            ->where('is_super_admin', false)
            ->count();

        // Recent tenants
        $recentTenants = Tenant::with('activeSubscription')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'subscription_status' => $tenant->subscription_status,
                    'is_active' => $tenant->is_active,
                    'created_at' => $tenant->created_at,
                    'users_count' => $tenant->users()->count(),
                    'branches_count' => $tenant->branches()->count(),
                ];
            });

        // Monthly revenue trend (last 12 months)
        $monthlyRevenue = Subscription::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(price) as revenue')
        )
            ->where('created_at', '>=', now()->subMonths(12))
            ->where('status', 'active')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Subscription distribution
        $subscriptionDistribution = Subscription::select('plan_slug', DB::raw('count(*) as count'))
            ->where('status', 'active')
            ->groupBy('plan_slug')
            ->get();

        return response()->json([
            'overview' => [
                'total_tenants' => $totalTenants,
                'active_tenants' => $activeTenants,
                'trial_tenants' => $trialTenants,
                'suspended_tenants' => $suspendedTenants,
                'active_subscriptions' => $activeSubscriptions,
                'expiring_soon' => $expiringSoon,
                'total_revenue' => $totalRevenue,
                'total_users' => $totalUsers,
            ],
            'growth' => [
                'new_tenants_30d' => $newTenantsLast30Days,
                'new_users_30d' => $newUsersLast30Days,
            ],
            'recent_tenants' => $recentTenants,
            'monthly_revenue' => $monthlyRevenue,
            'subscription_distribution' => $subscriptionDistribution,
        ]);
    }
}
