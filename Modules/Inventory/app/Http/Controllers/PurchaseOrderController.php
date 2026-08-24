<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Services\PurchaseOrderService;
use Modules\Inventory\Http\Requests\PurchaseOrderRequest;
use Modules\Inventory\Models\Supplier;
use Modules\Store\Models\Store;
use Modules\Account\Models\AccountAccount;

class PurchaseOrderController extends Controller
{
    protected PurchaseOrderService $poService;

    public function __construct(PurchaseOrderService $poService)
    {
        $this->poService = $poService;
    }

    public function index()
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $stores = Store::where('status', 'active')->orderBy('name')->get();
        $accounts = AccountAccount::where('is_active', true)->orderBy('name')->get();
        return view('inventory::purchase-orders.index', compact('suppliers', 'stores', 'accounts'));
    }

    public function dataTable(Request $request)
    {
        return $this->poService->getPoDataTable($request);
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $stores = Store::where('status', 'active')->orderBy('name')->get();
        $accounts = AccountAccount::where('is_active', true)->orderBy('name')->get();
        return view('inventory::purchase-orders.create', compact('suppliers', 'stores', 'accounts'));
    }

    public function store(PurchaseOrderRequest $request)
    {
        $result = $this->poService->savePo($request->validated());
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result, $result['status'] === 'success' ? 200 : 500);
        }
        return redirect()->route('purchase-orders.index')->with($result['status'], $result['message']);
    }

    public function show($id)
    {
        $result = $this->poService->getPoById($id);
        if ($result['status'] === 'error') {
            return redirect()->route('purchase-orders.index')->with('error', 'Purchase order not found.');
        }
        $purchase_order = $result['purchase_order'];
        $payments = \Modules\Inventory\Models\SupplierPayment::with('account')
            ->where('purchase_order_id', $purchase_order->id)
            ->orderByDesc('payment_date')
            ->get();
        $accounts = AccountAccount::where('is_active', true)->orderBy('name')->get();

        return view('inventory::purchase-orders.show', compact('purchase_order', 'payments', 'accounts'));
    }

    public function edit($id)
    {
        $result = $this->poService->getPoById($id);
        if ($result['status'] === 'error') {
            return redirect()->route('purchase-orders.index')->with('error', 'Purchase order not found.');
        }
        $purchase_order = $result['purchase_order'];
        if (!in_array($purchase_order->status, ['draft', 'ordered'])) {
            return redirect()->route('purchase-orders.show', $id)->with('error', 'Cannot edit a ' . $purchase_order->status . ' order.');
        }
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $stores = Store::where('status', 'active')->orderBy('name')->get();
        $accounts = AccountAccount::where('is_active', true)->orderBy('name')->get();
        return view('inventory::purchase-orders.edit', compact('purchase_order', 'suppliers', 'stores', 'accounts'));
    }

    public function update(PurchaseOrderRequest $request, $id)
    {
        $data = $request->validated();
        $data['purchase_order_id'] = $id;
        $result = $this->poService->savePo($data);
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result, $result['status'] === 'success' ? 200 : 500);
        }
        return redirect()->route('purchase-orders.index')->with($result['status'], $result['message']);
    }

    public function destroy($id)
    {
        $result = $this->poService->deletePo($id);
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'nullable|in:ordered,partially_received,received,cancelled',
            'payment_status' => 'nullable|in:unpaid,partial,paid',
        ]);

        if (!$request->filled('status') && !$request->filled('payment_status')) {
            $result = [
                'status' => 'error',
                'message' => 'Status or payment status is required.',
            ];

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json($result, 400);
            }

            return redirect()->back()->with('error', $result['message']);
        }

        $result = $this->poService->updateStatus(
            $id,
            $request->input('status'),
            $request->input('payment_status')
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result, $result['status'] === 'success' ? 200 : 400);
        }

        return redirect()->back()->with($result['status'], $result['message']);
    }

    public function searchProducts(Request $request)
    {
        return response()->json($this->poService->searchProducts($request));
    }
}