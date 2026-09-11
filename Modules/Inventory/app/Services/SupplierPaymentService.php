<?php

namespace Modules\Inventory\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Account\Models\AccountAccount;
use Modules\Account\Models\AccountCategory;
use Modules\Account\Services\AccountTransactionService;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Inventory\Models\Supplier;
use Modules\Inventory\Models\SupplierPayment;
use Modules\Store\Models\Store;
use Yajra\DataTables\DataTables;

class SupplierPaymentService
{
    public function __construct(private AccountTransactionService $transactionService) {}

    public function getDataTable(Request $request)
    {
        $query = SupplierPayment::with(['supplier', 'purchaseOrder', 'account'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        return DataTables::of($query)
            ->addColumn('supplier_name', fn (SupplierPayment $payment) => $payment->supplier?->name ?? '-')
            ->addColumn('po_number', fn (SupplierPayment $payment) => $payment->purchaseOrder?->po_number ?? '-')
            ->addColumn('account_name', fn (SupplierPayment $payment) => $payment->account?->name ?? '-')
            ->addColumn('payment_method', fn (SupplierPayment $payment) => ucfirst(str_replace('_', ' ', $payment->payment_method)))
            ->editColumn('amount', fn (SupplierPayment $payment) => number_format((float) $payment->amount, 2))
            ->editColumn('payment_date', fn (SupplierPayment $payment) => $payment->payment_date?->format('d M Y') ?? '-')
            ->addColumn('action', function (SupplierPayment $payment) {
                return view('components.action-buttons', [
                'permission' => 'supplier-payments',
                'entityLabel' => 'Supplier Payment',
                    'id' => $payment->id,
                    'delete' => 'supplierPaymentDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function save(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $account = AccountAccount::findOrFail($data['account_id']);
                $amount = (float) $data['amount'];

                if ((float) $account->current_balance < $amount) {
                    return ApiResponse::error(
                        'Insufficient balance in "'.$account->name.'". Available: ৳'.number_format((float) $account->current_balance, 2),
                        500
                    );
                }

                if (! empty($data['purchase_order_id'])) {
                    $po = PurchaseOrder::find($data['purchase_order_id']);
                    if (! $po) {
                        return ApiResponse::notFound('Purchase order not found.');
                    }
                    $due = max(0, (float) $po->total_amount - (float) $po->paid_amount);
                    if ($amount > $due) {
                        return ApiResponse::error('Amount exceeds the due balance (৳'.number_format($due, 2).') for this purchase order.', 500);
                    }
                }

                $data['payment_no'] = SupplierPayment::generatePaymentNo();
                $data['payment_method'] = $data['payment_method'] ?? $account->type;
                $data['created_by'] = Auth::id();

                if (empty($data['store_id']) && ! empty($data['purchase_order_id'])) {
                    $po = PurchaseOrder::find($data['purchase_order_id']);
                    $data['store_id'] = $po?->store_id;
                }

                $payment = SupplierPayment::create($data);

                $category = AccountCategory::where('system_key', 'product-purchase')->first()
                    ?? $this->transactionService->ensureCategory('product-purchase', 'Product Purchase', 'cost_of_goods_sold');

                $transaction = $this->transactionService->postSupplierPayment($payment, $account, $category, $amount);
                $payment->update(['transaction_id' => $transaction->id]);

                if ($payment->purchase_order_id) {
                    $this->updatePurchasePaymentStatus($payment->purchase_order_id);
                }

                return ApiResponse::created($payment->fresh(['supplier', 'purchaseOrder', 'account']), 'Supplier payment recorded. Balance updated for "'.$account->name.'".');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving supplier payment: '.$e->getMessage(), 500);
        }
    }

    public function find(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(SupplierPayment::with(['supplier', 'purchaseOrder', 'account'])->findOrFail($id));
        } catch (\Exception) {
            return ApiResponse::notFound('Supplier payment not found.');
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $payment = SupplierPayment::findOrFail($id);
                $poId = $payment->purchase_order_id;

                if ($payment->transaction) {
                    if ($account = $payment->account) {
                        $account->increment('current_balance', (float) $payment->amount);
                    }
                    $payment->transaction->delete();
                }

                $payment->delete();

                if ($poId) {
                    $this->updatePurchasePaymentStatus($poId);
                }

                return ApiResponse::success(null, 'Supplier payment deleted and account impact reversed.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting supplier payment: '.$e->getMessage(), 500);
        }
    }

    public function getStores()
    {
        return Store::where('status', 'active')->orderBy('name')->get();
    }

    public function getSuppliers()
    {
        return Supplier::where('status', 'active')->orderBy('name')->get();
    }

    public function getPurchaseOrders()
    {
        return PurchaseOrder::orderBy('po_number')->get(['id', 'po_number', 'supplier_id', 'store_id', 'total_amount']);
    }

    public function getAccounts()
    {
        return AccountAccount::where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Recalculate a Purchase Order's payment_status from its payment records.
     */
    public function updatePurchasePaymentStatus(int $purchaseOrderId): void
    {
        $po = PurchaseOrder::find($purchaseOrderId);
        if (! $po) {
            return;
        }

        $paid = (float) SupplierPayment::where('purchase_order_id', $po->id)->sum('amount');
        $total = max(0, (float) $po->total_amount);

        if ($paid <= 0) {
            $po->update(['payment_status' => 'unpaid']);
        } elseif ($paid >= $total) {
            $po->update(['payment_status' => 'paid']);
        } else {
            $po->update(['payment_status' => 'partial']);
        }
    }
}
