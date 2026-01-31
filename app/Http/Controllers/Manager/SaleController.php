<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Get all sales for the branch
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        if (! $branch) {
            return response()->json([
                'message' => 'No branch assigned',
                'sales' => [],
            ]);
        }

        $sales = Sale::where('branch_id', $branch->id)
            ->with(['user:id,name', 'items.product:id,name', 'payments'])
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->payment_method, function ($query) use ($request) {
                $query->where('payment_method', $request->payment_method);
            })
            ->when($request->start_date, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->end_date);
            })
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($sales);
    }

    /**
     * Get a single sale details
     */
    public function show(Request $request, Sale $sale): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        // Ensure the sale belongs to the manager's branch
        if ($sale->branch_id !== $branch->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $sale->load(['user:id,name', 'items.product:id,name,sku', 'payments']);

        return response()->json($sale);
    }

    /**
     * Approve a sale (if approval workflow is implemented)
     */
    public function approve(Request $request, Sale $sale): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        // Ensure the sale belongs to the manager's branch
        if ($sale->branch_id !== $branch->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Update sale status or add approval logic
        // This is a placeholder - implement based on your business logic
        $sale->update([
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Sale approved successfully',
            'sale' => $sale,
        ]);
    }

    /**
     * Void a sale
     */
    public function void(Request $request, Sale $sale): JsonResponse
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        // Ensure the sale belongs to the manager's branch
        if ($sale->branch_id !== $branch->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($sale->status === Sale::STATUS_CANCELLED) {
            return response()->json([
                'message' => 'Sale is already voided',
            ], 400);
        }

        $sale->update([
            'status' => Sale::STATUS_CANCELLED,
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason ?? 'Voided by manager',
        ]);

        return response()->json([
            'message' => 'Sale voided successfully',
            'sale' => $sale,
        ]);
    }
}
