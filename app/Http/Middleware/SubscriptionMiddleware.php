<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $user = auth()->user();

        // Super admins bypass subscription checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has a tenant
        if (!$user->tenant_id || !$user->tenant) {
            return response()->json([
                'message' => 'No tenant association found.'
            ], 403);
        }

        $tenant = $user->tenant;

        // Check if tenant can access the system
        if (!$tenant->canAccess()) {
            $message = 'Your subscription has expired or been suspended. Please contact support.';
            
            if ($tenant->subscription_status === 'trial' && $tenant->trial_ends_at && $tenant->trial_ends_at->isPast()) {
                $message = 'Your trial period has ended. Please subscribe to continue using the service.';
            }

            return response()->json([
                'message' => $message,
                'subscription_status' => $tenant->subscription_status,
                'trial_ends_at' => $tenant->trial_ends_at,
            ], 403);
        }

        return $next($request);
    }
}
