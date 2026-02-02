<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $coupons = Coupon::where('tenant_id', $tenantId)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                } elseif ($status === 'expired') {
                    $query->where('end_date', '<', now());
                }
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Stats
        $stats = [
            'total_coupons' => Coupon::where('tenant_id', $tenantId)->count(),
            'active_coupons' => Coupon::where('tenant_id', $tenantId)->where('is_active', true)->count(),
            'total_uses' => Coupon::where('tenant_id', $tenantId)->sum('times_used'),
            'expired' => Coupon::where('tenant_id', $tenantId)->where('end_date', '<', now())->count(),
        ];

        return Inertia::render('Admin/Customers/Coupons', [
            'coupons' => $coupons,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'type']),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons')->where('tenant_id', $user->tenant_id),
            ],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_per_customer' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ]);

        $validated['tenant_id'] = $user->tenant_id;
        $validated['code'] = strtoupper($validated['code']);

        Coupon::create($validated);

        return back()->with('success', 'Coupon created successfully.');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $this->authorizeAccess($coupon);
        $user = $request->user();

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons')->where('tenant_id', $user->tenant_id)->ignore($coupon->id),
            ],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_per_customer' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $coupon->update($validated);

        return back()->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Request $request, Coupon $coupon)
    {
        $this->authorizeAccess($coupon);

        $coupon->delete();

        return back()->with('success', 'Coupon deleted successfully.');
    }

    public function generateCode(): \Illuminate\Http\JsonResponse
    {
        $code = strtoupper(Str::random(8));

        return response()->json(['code' => $code]);
    }

    protected function authorizeAccess(Coupon $coupon): void
    {
        if ($coupon->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
    }
}
