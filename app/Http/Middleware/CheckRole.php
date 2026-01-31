<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Allow if user has any of the specified roles
        if (in_array($request->user()->role, $roles)) {
            return $next($request);
        }

        // Redirect to appropriate dashboard based on role
        return match ($request->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'cashier' => redirect()->route('cashier.dashboard'),
            'manager' => redirect()->route('manager.mobile-only'),
            default => redirect()->route('user.dashboard'),
        };
    }
}
