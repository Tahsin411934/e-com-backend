<?php

namespace Modules\Inventory\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Account\Services\AccountTransactionService;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Modules\Inventory\Models\InventoryLocation;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\InventoryStock;
use Modules\Inventory\Models\PurchaseOrder;
use Yajra\DataTables\DataTables;

class PurchaseOrderService
{
    public function __construct(
        private AccountTransactionService $transactionService,
        private SupplierPaymentService $supplierPaymentService,
    ) {}

    public function getPoDataTable(Request $request)
    {
        $query = PurchaseOrder::forCurrentStore()
            ->with(['supplier', 'store'])
            ->orderByDesc('created_at');

        if ($request->store_id) {
            $query->where('store_id', $request->store_id);
        }

        return DataTables::of($query)
            ->editColumn('status', function (PurchaseOrder $po) {
                $colors = [
                    'draft' => 'bg-gray-100 text-gray-700',
                    'ordered' => 'bg-blue-100 text-blue-700',
                    'partially_received' => 'bg-yellow-100 text-yellow-700',
                    'received' => 'bg-green-100 text-green-700',
                    'returned' => 'bg-orange-100 text-orange-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                ];
                $class = $colors[$po->status] ?? 'bg-gray-100';

                return '<span class="px-2 py-1 rounded-full text-xs font-medium '.$class.'">'.ucfirst(str_replace('_', ' ', $po->status)).'</span>';
            })
            ->editColumn('payment_status', function (PurchaseOrder $po) {
                $colors = [
                    'unpaid' => 'bg-red-100 text-red-700',
                    'partial' => 'bg-yellow-100 text-yellow-700',
                    'paid' => 'bg-green-100 text-green-700',
                ];
                $class = $colors[$po->payment_status] ?? 'bg-gray-100';

                return '<span class="px-2 py-1 rounded-full text-xs font-medium '.$class.'">'.ucfirst(str_replace('_', ' ', $po->payment_status)).'</span>';
            })
            ->editColumn('total_amount', function (PurchaseOrder $po) {
                return number_format($po->total_amount, 2);
            })
            ->addColumn('paid_amount', function (PurchaseOrder $po) {
                return number_format((float) $po->paid_amount, 2);
            })
            ->addColumn('due_amount', function (PurchaseOrder $po) {
                return number_format(max(0, (float) $po->total_amount - (float) $po->paid_amount), 2);
            })
            ->addColumn('supplier_name', function (PurchaseOrder $po) {
                return $po->supplier ? $po->supplier->name : '-';
            })
            ->addColumn('store_name', function (PurchaseOrder $po) {
                return $po->store ? $po->store->name : '-';
            })
            ->editColumn('created_at', function (PurchaseOrder $po) {
                return $po->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (PurchaseOrder $po) {
                $html = '';

                // Quick status workflow buttons (inline) — gated by purchase-orders.edit
                if (auth()->user()->hasPermission('purchase-orders.edit')) {
                    if ($po->status === 'draft') {
                        $html .= '<button onclick="updatePoStatus('.$po->id.', \'ordered\')" class="bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700 mr-1" title="Mark as Ordered"><i class="fas fa-check"></i> Order</button>';
                    }
                    if (in_array($po->status, ['ordered', 'partially_received'])) {
                        $html .= '<button onclick="updatePoStatus('.$po->id.', \'received\')" class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700 mr-1" title="Mark as Received"><i class="fas fa-check-double"></i> Receive</button>';
                    }
                    if (in_array($po->status, ['draft', 'ordered'])) {
                        $html .= '<button onclick="updatePoStatus('.$po->id.', \'cancelled\')" class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600 mr-1" title="Cancel Order"><i class="fas fa-times"></i></button>';
                    }
                }
                if ($po->payment_status !== 'paid' && $po->status !== 'cancelled' && auth()->user()->hasPermission('supplier-payments.create')) {
                    $html .= '<a href="'.route('purchase-orders.show', $po->id).'" class="bg-indigo-600 text-white px-2 py-1 rounded text-xs hover:bg-indigo-700 mr-1 transition" title="Record Payment"><i class="fas fa-dollar-sign"></i> Pay</a>';
                }

                // Create a purchase return for received orders — gated by purchase-returns.create.
                if (in_array($po->status, ['received', 'partially_received'], true) && auth()->user()->hasPermission('purchase-returns.create')) {
                    $html .= '<a href="'.route('purchase-returns.create', ['purchase_order_id' => $po->id]).'" class="bg-orange-500 text-white px-2 py-1 rounded text-xs hover:bg-orange-600 mr-1 transition" title="Create Purchase Return"><i class="fas fa-undo-alt"></i> Return</a>';
                }

                // Standard action buttons (hide edit/delete for locked statuses)
                $html .= view('components.action-buttons', [
                'permission' => 'purchase-orders',
                'entityLabel' => 'Purchase Order',
                    'id' => $po->id,
                    'show' => true,
                    'showUrl' => route('purchase-orders.show', ':id'),
                    'editUrl' => in_array($po->status, ['draft', 'ordered'], true)
                        ? route('purchase-orders.edit', ':id')
                        : null,
                    'deleteUrl' => in_array($po->status, ['draft', 'ordered', 'cancelled'], true)
                        ? route('purchase-orders.destroy', ':id')
                        : null,
                ])->render();

                return $html;
            })
            ->rawColumns(['action', 'status', 'payment_status'])
            ->make(true);
    }

    public function savePo(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $poId = $data['purchase_order_id'] ?? null;
                unset($data['purchase_order_id']);

                $items = $data['items'] ?? [];
                unset($data['items']);

                if (empty($data['po_number'])) {
                    $data['po_number'] = PurchaseOrder::generatePoNumber();
                }

                $data['created_by'] = auth()->id();

                if ($poId) {
                    $po = PurchaseOrder::findOrFail($poId);
                    $po->update($data);
                    $message = 'Purchase order updated successfully.';
                } else {
                    $data['status'] = $data['status'] ?? 'draft';
                    $po = PurchaseOrder::create($data);
                    $message = 'Purchase order created successfully.';
                }

                // Soft-delete old items individually so each deletion is audited.
                $po->items()->get()->each->delete();
                $totalAmount = 0;
                foreach ($items as $item) {
                    $subtotal = ($item['quantity'] ?? 0) * ($item['unit_cost'] ?? 0);
                    $item['subtotal'] = $subtotal;
                    $item['received_quantity'] = $item['received_quantity'] ?? 0;
                    $item['tax'] = $item['tax'] ?? 0;
                    $item['discount'] = $item['discount'] ?? 0;
                    $po->items()->create($item);
                    $totalAmount += $subtotal;
                }

                $totalAmount += ($data['shipping_cost'] ?? 0) + ($data['tax_amount'] ?? 0) - ($data['discount_amount'] ?? 0);
                $po->update(['total_amount' => $totalAmount]);

                // If an advance / paid amount was submitted with this NEW PO, record it
                // immediately against the selected account (balance decreases).
                // (On edit we ignore these fields to avoid double-charging; additional
                //  payments are recorded from the PO page via Supplier Payments.)
                $paidAmount = (float) ($data['paid_amount'] ?? 0);
                $accountId = $data['account_id'] ?? null;
                if (! $poId && $paidAmount > 0 && $accountId) {
                    $this->recordPoPayment($po, $paidAmount, (string) $accountId);
                } else {
                    $this->supplierPaymentService->updatePurchasePaymentStatus($po->id);
                }

                if ($poId) {
                    return ApiResponse::success($po->fresh()->load(['supplier', 'store', 'items.variant']), $message);
                }

                return ApiResponse::created($po->fresh()->load(['supplier', 'store', 'items.variant']), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving purchase order: '.$e->getMessage(), 500);
        }
    }

    public function getPoById(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(
                PurchaseOrder::with(['supplier', 'store', 'items.variant.product', 'creator'])->findOrFail($id)
            );
        } catch (\Exception) {
            return ApiResponse::notFound('Purchase order not found.');
        }
    }

    public function deletePo(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $po = PurchaseOrder::findOrFail($id);
                // Received orders are locked — deleting them would orphan stock.
                // Create a purchase return instead.
                if (in_array($po->status, ['received', 'partially_received', 'returned'], true)) {
                    return ApiResponse::error('Received or returned orders cannot be deleted. Create a purchase return or adjustment instead.', 500);
                }
                $po->delete();

                return ApiResponse::success(null, 'Purchase order deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting purchase order: '.$e->getMessage(), 500);
        }
    }

    public function updateStatus(int $id, ?string $status = null, ?string $paymentStatus = null): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id, $status, $paymentStatus) {
                $po = PurchaseOrder::findOrFail($id);

                if (! $status && ! $paymentStatus) {
                    return ApiResponse::error('Nothing to update.', 500);
                }

                if ($status) {
                    $validTransitions = [
                        'draft' => ['ordered', 'cancelled'],
                        'ordered' => ['partially_received', 'received', 'cancelled'],
                        'partially_received' => ['received', 'returned'],
                        'received' => ['returned'],
                        'returned' => [],
                        'cancelled' => [],
                    ];

                    if (! in_array($status, $validTransitions[$po->status] ?? [])) {
                        return ApiResponse::error('Cannot change status from "'.$po->status.'" to "'.$status.'".', 500);
                    }

                    $po->update(['status' => $status]);

                    if ($status === 'received') {
                        $this->processStockUpdate($po);
                        $po->update(['received_date' => now()->toDateString()]);
                    }
                }

                if ($paymentStatus) {
                    // Payment status is now derived from actual supplier_payments records.
                    $this->supplierPaymentService->updatePurchasePaymentStatus($po->id);
                    $po->refresh();

                    if ($paymentStatus === 'paid' && $po->payment_status !== 'paid') {
                        return ApiResponse::error(
                            'This order is not fully paid yet. Record the remaining amount as a Supplier Payment from the order page to mark it Paid.',
                            500
                        );
                    }

                    $po->update(['payment_status' => $po->payment_status]);
                }

                $message = [];
                if ($status) {
                    $message[] = 'Status updated to "'.ucfirst(str_replace('_', ' ', $status)).'".';
                }
                if ($paymentStatus) {
                    $message[] = 'Payment status updated to "'.ucfirst(str_replace('_', ' ', $paymentStatus)).'".';
                }

                return ApiResponse::success(
                    $po->fresh()->load(['supplier', 'store', 'items.variant']),
                    implode(' ', $message)
                );
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error updating status: '.$e->getMessage(), 500);
        }
    }

    /**
     * Raw model lookup for show/edit views (findOrFail → automatic 404).
     */
    public function getPoModel(int $id): PurchaseOrder
    {
        return PurchaseOrder::forCurrentStore()
            ->with(['supplier', 'store', 'items.variant.product', 'creator'])
            ->findOrFail($id);
    }

    protected function recordPoPayment(PurchaseOrder $po, float $amount, string $accountId): array
    {
        $response = $this->supplierPaymentService->save([
            'supplier_id' => $po->supplier_id,
            'purchase_order_id' => $po->id,
            'store_id' => $po->store_id,
            'account_id' => (int) $accountId,
            'amount' => $amount,
            'payment_date' => now()->toDateString(),
            'note' => 'Advance payment recorded during purchase order creation.',
        ]);

        $payload = $response->getData(true);

        if (($payload['status'] ?? '') !== 'success') {
            throw new \RuntimeException($payload['message'] ?? 'Failed to record advance payment.');
        }

        return $payload;
    }

    protected function processStockUpdate(PurchaseOrder $po): void
    {
        $location = InventoryLocation::where('store_id', $po->store_id)->first();

        if (! $location) {
            $location = InventoryLocation::create([
                'store_id' => $po->store_id,
                'name' => $po->store->name.' - Main',
                'location_type' => 'warehouse',
                'status' => 'active',
            ]);
        }

        foreach ($po->items as $item) {
            $receivedQty = $item->quantity - $item->received_quantity;
            if ($receivedQty <= 0) {
                continue;
            }

            $item->update(['received_quantity' => $item->quantity]);

            $stock = InventoryStock::firstOrCreate(
                ['location_id' => $location->id, 'variant_id' => $item->variant_id],
                ['quantity_on_hand' => 0, 'quantity_reserved' => 0, 'reorder_point' => 0]
            );
            $stock->increment('quantity_on_hand', $receivedQty);

            InventoryMovement::create([
                'location_id' => $location->id,
                'variant_id' => $item->variant_id,
                'movement_type' => 'purchase',
                'quantity' => $receivedQty,
                'reference_type' => PurchaseOrder::class,
                'reference_id' => $po->id,
                'note' => 'PO '.$po->po_number.' received',
                'created_by' => auth()->id(),
            ]);

            if ($item->unit_cost > 0) {
                ProductVariant::where('id', $item->variant_id)->update(['cost_price' => $item->unit_cost]);
            }
        }
    }

    /**
     * Search products by name or SKU for the PO form
     */
    public function searchProducts(Request $request): array
    {
        $search = $request->get('q', '');
        $results = Product::where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhereHas('variants', function ($qv) use ($search) {
                    $qv->where('sku', 'like', "%{$search}%");
                });
        })
            ->with(['variants' => function ($q) {
                $q->select('id', 'product_id', 'name', 'sku', 'sale_price', 'cost_price');
            }])
            ->where('status', 'active')
            ->limit(20)
            ->get(['id', 'name']);

        $items = [];
        foreach ($results as $product) {
            foreach ($product->variants as $variant) {
                $items[] = [
                    'id' => $variant->id,
                    'text' => $product->name.' - '.$variant->name.' ('.$variant->sku.')',
                    'product_name' => $product->name,
                    'variant_name' => $variant->name,
                    'sku' => $variant->sku,
                    'sale_price' => $variant->sale_price,
                    'cost_price' => $variant->cost_price,
                ];
            }
        }

        return ['results' => $items];
    }
}
