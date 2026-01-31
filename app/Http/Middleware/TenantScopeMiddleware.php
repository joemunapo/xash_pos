<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantScopeMiddleware
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

        // Super admins bypass tenant scoping
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Regular users must have a tenant_id
        if (!$user->tenant_id) {
            return response()->json([
                'message' => 'No tenant association found.'
            ], 403);
        }

        // Set tenant_id in session for global scoping
        session(['tenant_id' => $user->tenant_id]);

        return $next($request);
    }
}
