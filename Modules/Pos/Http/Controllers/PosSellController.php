<?php

namespace Modules\Pos\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Pos\Services\PosRegisterService;
use Modules\Pos\Services\PosSellService;
use Modules\Pos\Services\PosShiftService;

class PosSellController extends Controller
{
    public function __construct(private PosSellService $sellService, private PosRegisterService $registerService, private PosShiftService $shiftService) {}

    /**
     * Show the POS create sell interface
     */
    public function index()
    {
        $registers = $this->registerService->getAllActiveRegisters();
        $openShifts = $this->shiftService->getOpenShifts();

        return view('pos::sells.index', compact('registers', 'openShifts'));
    }

    /**
     * Search customers by phone/name
     */
    public function searchCustomers(Request $request)
    {
        return $this->sellService->searchCustomers($request);
    }

    /**
     * Search products by name/sku
     */
    public function searchProducts(Request $request)
    {
        return $this->sellService->searchProducts($request);
    }

    /**
     * Process and complete the sale
     */
    public function processSale(Request $request)
    {
        return $this->sellService->processSale($request);
    }

    /**
     * Get recent sales for the current register
     */
    public function getRecentSales(Request $request)
    {
        return $this->sellService->getRecentSales($request);
    }
}
