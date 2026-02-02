<?php

namespace App\Http\Controllers\SuperAdmin\Web;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(): \Inertia\Response
    {
        $overview = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'trial_tenants' => Tenant::where('subscription_status', 'trial')->count(),
            'suspended_tenants' => Tenant::where('subscription_status', 'suspended')->count(),
            'active_subscriptions' => Subscription::active()->count(),
            'expiring_soon' => Subscription::expiringSoon(7)->count(),
            'total_revenue' => Subscription::where('status', 'active')->sum('price'),
            'total_users' => User::where('is_super_admin', false)->count(),
        ];

        $growth = [
            'new_tenants_30d' => Tenant::where('created_at', '>=', now()->subDays(30))->count(),
            'new_users_30d' => User::where('created_at', '>=', now()->subDays(30))
                ->where('is_super_admin', false)
                ->count(),
        ];

        $recentTenants = Tenant::with('activeSubscription')
            ->withCount(['users', 'branches'])
            ->latest()
            ->limit(10)
            ->get();

        $monthlyRevenue = Subscription::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(price) as revenue')
        )
            ->where('created_at', '>=', now()->subMonths(12))
            ->where('status', 'active')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $subscriptionDistribution = Subscription::select('plan_name', DB::raw('count(*) as count'))
            ->where('status', 'active')
            ->groupBy('plan_name')
            ->get();

        return Inertia::render('SuperAdmin/Dashboard', [
            'overview' => $overview,
            'growth' => $growth,
            'recentTenants' => $recentTenants,
            'monthlyRevenue' => $monthlyRevenue,
            'subscriptionDistribution' => $subscriptionDistribution,
        ]);
    }
}
