<?php

namespace App\Http\Controllers\Web\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    /**
     * Display cashier's sales history (to be implemented in Phase 5)
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $branch = $user->primaryBranch();

        // Basic implementation - will be enhanced in Phase 5
        $sales = Sale::where('branch_id', $branch->id)
            ->where('user_id', $user->id)
            ->where('status', Sale::STATUS_COMPLETED)
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Cashier/Sales/Index', [
            'sales' => $sales,
        ]);
    }

    /**
     * Display sale details (to be implemented in Phase 5)
     */
    public function show(Request $request, Sale $sale): Response
    {
        $user = $request->user();

        // Ensure cashier can only view their own sales
        if ($sale->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $sale->load(['items.product', 'user']);

        return Inertia::render('Cashier/Sales/Show', [
            'sale' => $sale,
        ]);
    }
}
