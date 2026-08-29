<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Account\Models\AccountAccount;
use Modules\Inventory\Http\Requests\PurchaseOrderRequest;
use Modules\Inventory\Models\Supplier;
use Modules\Inventory\Models\SupplierPayment;
use Modules\Inventory\Services\PurchaseOrderService;
use Modules\Store\Models\Store;

class PurchaseOrderController extends Controller
{
    public function __construct(private PurchaseOrderService $poService) {}

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
        $response = $this->poService->savePo($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }

        $payload = $response->getData(true);

        return redirect()->route('purchase-orders.index')->with($payload['status'] ?? 'error', $payload['message'] ?? 'Done.');
    }

    public function show($id)
    {
        $purchase_order = $this->poService->getPoModel((int) $id);
        $payments = SupplierPayment::with('account')
            ->where('purchase_order_id', $purchase_order->id)
            ->orderByDesc('payment_date')
            ->get();
        $accounts = AccountAccount::where('is_active', true)->orderBy('name')->get();

        return view('inventory::purchase-orders.show', compact('purchase_order', 'payments', 'accounts'));
    }

    public function edit($id)
    {
        $purchase_order = $this->poService->getPoModel((int) $id);

        if (! in_array($purchase_order->status, ['draft', 'ordered'])) {
            return redirect()->route('purchase-orders.show', $id)->with('error', 'Cannot edit a '.$purchase_order->status.' order.');
        }

        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $stores = Store::where('status', 'active')->orderBy('name')->get();
        $accounts = AccountAccount::where('is_active', true)->orderBy('name')->get();

        return view('inventory::purchase-orders.edit', compact('purchase_order', 'suppliers', 'stores', 'accounts'));
    }

    public function update(PurchaseOrderRequest $request, $id)
    {
        $response = $this->poService->savePo($request->validated() + ['purchase_order_id' => $id]);

        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }

        $payload = $response->getData(true);

        return redirect()->route('purchase-orders.index')->with($payload['status'] ?? 'error', $payload['message'] ?? 'Done.');
    }

    public function destroy($id)
    {
        return $this->poService->deletePo((int) $id);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'nullable|in:ordered,partially_received,received,cancelled',
            'payment_status' => 'nullable|in:unpaid,partial,paid',
        ]);

        $response = $this->poService->updateStatus(
            (int) $id,
            $request->input('status'),
            $request->input('payment_status')
        );

        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }

        $payload = $response->getData(true);

        return redirect()->back()->with($payload['status'] ?? 'error', $payload['message'] ?? 'Done.');
    }

    public function searchProducts(Request $request)
    {
        return $this->poService->searchProducts($request);
    }
}
