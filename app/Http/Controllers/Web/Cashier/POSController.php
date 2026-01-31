<?php

namespace App\Http\Controllers\Web\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class POSController extends Controller
{
    /**
     * Display POS terminal (to be implemented in Phase 5)
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Cashier/POS', [
            'message' => 'POS Terminal - Coming Soon (Phase 5)',
        ]);
    }

    /**
     * Process sale (to be implemented in Phase 5)
     */
    public function processSale(Request $request)
    {
        return back()->with('info', 'POS processing will be implemented in Phase 5');
    }
}
