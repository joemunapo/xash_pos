<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Create a new customer from POS
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required_without:email', 'nullable', 'string', 'max:20'],
            'email' => ['required_without:phone', 'nullable', 'email', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();

        try {
            // Check for existing customer by phone or email within tenant
            $existing = Customer::where('tenant_id', $user->tenant_id)
                ->where(function ($query) use ($request) {
                    if ($request->filled('phone')) {
                        $query->orWhere('phone', $request->phone);
                    }
                    if ($request->filled('email')) {
                        $query->orWhere('email', $request->email);
                    }
                })
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'Customer already exists',
                    'customer' => $existing,
                    'existing' => true,
                ]);
            }

            $customer = Customer::create([
                'tenant_id' => $user->tenant_id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'loyalty_tier' => 'bronze',
                'loyalty_points' => 0,
                'is_active' => true,
                'member_since' => now(),
                'qr_code' => Str::uuid(),
            ]);

            return response()->json([
                'message' => 'Customer created successfully',
                'customer' => $customer,
                'existing' => false,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create customer. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
