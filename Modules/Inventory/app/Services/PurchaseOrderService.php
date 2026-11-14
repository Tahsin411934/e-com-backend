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
use Modules\Store\Support\CurrentStore;
use Modules\Store\Models\Store;
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
                        $html .= '<button type="button" class="js-purchase-order-status bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700 mr-1" data-id="'.$po->id.'" data-status="ordered" title="Mark as Ordered"><i class="fas fa-check"></i> Order</button>';
                    }
                    if (in_array($po->status, ['ordered', 'partially_received'])) {
                        $html .= '<button type="button" class="js-purchase-order-status bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700 mr-1" data-id="'.$po->id.'" data-status="received" title="Mark as Received"><i class="fas fa-check-double"></i> Receive</button>';
                    }
                    if (in_array($po->status, ['draft', 'ordered'])) {
                        $html .= '<button type="button" class="js-purchase-order-status bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600 mr-1" data-id="'.$po->id.'" data-status="cancelled" title="Cancel Order"><i class="fas fa-times"></i></button>';
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
                if (($storeId = CurrentStore::id()) !== null) {
                    $data['store_id'] = $storeId;
                }

                $variantIds = collect($items)->pluck('variant_id')->map(fn ($id) => (int) $id)->unique()->values();
                $validVariantIds = ProductVariant::query()
                    ->whereIn('id', $variantIds)
                    ->where('status', 'active')
                    ->whereHas('product', fn ($query) => $query->where(function ($scope) use ($data) {
                        $scope->whereNull('store_id')->orWhere('store_id', $data['store_id']);
                    }))
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id);
                if ($validVariantIds->count() !== $variantIds->count()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => 'All purchase variants must be active and belong to the selected store.',
                    ]);
                }

                if ($poId) {
                    $po = PurchaseOrder::forCurrentStore()->findOrFail($poId);
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors(), 'Purchase order items are invalid.');
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving purchase order: '.$e->getMessage(), 500);
        }
    }

    public function getPoById(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(
                PurchaseOrder::forCurrentStore()->with(['supplier', 'store', 'items.variant.product', 'creator'])->findOrFail($id)
            );
        } catch (\Exception) {
            return ApiResponse::notFound('Purchase order not found.');
        }
    }

    public function deletePo(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $po = PurchaseOrder::forCurrentStore()->lockForUpdate()->findOrFail($id);
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
                $po = PurchaseOrder::forCurrentStore()->lockForUpdate()->findOrFail($id);

                if (! $status && ! $paymentStatus) {
                    return ApiResponse::error('Nothing to update.', 422);
                }

                if ($status) {
                    $validTransitions = [
                        'draft' => ['ordered', 'cancelled'],
                        'ordered' => ['received', 'cancelled'],
                        'partially_received' => ['received', 'returned'],
                        'received' => ['returned'],
                        'returned' => [],
                        'cancelled' => [],
                    ];

                    if (! in_array($status, $validTransitions[$po->status] ?? [])) {
                        return ApiResponse::error('Cannot change status from "'.$po->status.'" to "'.$status.'".', 422);
                    }

                    if ($status === 'received') {
                        $this->processStockUpdate($po);
                    } else {
                        $po->update(['status' => $status]);
                    }
                }

                if ($paymentStatus) {
                    // Payment status is now derived from actual supplier_payments records.
                    $this->supplierPaymentService->updatePurchasePaymentStatus($po->id);
                    $po->refresh();

                    if ($paymentStatus === 'paid' && $po->payment_status !== 'paid') {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'payment_status' => 'This order is not fully paid yet. Record the remaining amount as a Supplier Payment from the order page to mark it Paid.',
                        ]);
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors(), 'Purchase order status could not be updated.');
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

    public function receiveItems(int $id, array $receivedQuantities): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id, $receivedQuantities) {
                $po = PurchaseOrder::forCurrentStore()->lockForUpdate()->findOrFail($id);
                if (! in_array($po->status, ['ordered', 'partially_received'], true)) {
                    return ApiResponse::error('Only ordered or partially received purchase orders can receive stock.', 422);
                }

                $result = $this->processStockUpdate($po, $receivedQuantities);
                if ($result['received_units'] < 1) {
                    return ApiResponse::error('Enter a received quantity for at least one item.', 422);
                }

                return ApiResponse::success(
                    $po->fresh()->load(['supplier', 'store', 'items.variant']),
                    $result['received_units'].' unit(s) received. Purchase order is now '.str_replace('_', ' ', $result['status']).'.'
                );
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors(), 'Received quantities are invalid.');
        } catch (\Exception $e) {
            return ApiResponse::error('Error receiving purchase order items: '.$e->getMessage(), 500);
        }
    }

    protected function processStockUpdate(PurchaseOrder $po, ?array $requestedQuantities = null): array
    {
        $items = $po->items()->lockForUpdate()->get();
        if ($items->isEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'items' => 'This purchase order has no items to receive.',
            ]);
        }

        if ($requestedQuantities !== null) {
            $validItemIds = $items->pluck('id')->map(fn ($itemId) => (string) $itemId)->all();
            $unknownItemIds = array_diff(array_map('strval', array_keys($requestedQuantities)), $validItemIds);
            if ($unknownItemIds) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'received_quantities' => 'One or more received quantities do not belong to this purchase order.',
                ]);
            }
        }

        $received = [];
        foreach ($items as $item) {
            $remaining = max(0, (int) $item->quantity - (int) $item->received_quantity);
            $quantity = $requestedQuantities === null
                ? $remaining
                : (int) ($requestedQuantities[$item->id] ?? 0);
            if ($quantity > $remaining) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'received_quantities.'.$item->id => 'Cannot receive more than the '.$remaining.' unit(s) still expected for this item.',
                ]);
            }
            $received[$item->id] = $quantity;
        }

        $receivedUnits = array_sum($received);
        if ($receivedUnits < 1) {
            if ($requestedQuantities === null) {
                throw \Illuminate\Validation\ValidationException::withMessages(['items' => 'There are no remaining units to receive.']);
            }

            return ['received_units' => 0, 'status' => $po->status];
        }

        $store = Store::query()->lockForUpdate()->find($po->store_id);
        if (! $store) {
            throw new \RuntimeException('The purchase order store could not be found.');
        }

        $location = InventoryLocation::query()
            ->where('store_id', $po->store_id)
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        if (! $location) {
            $location = InventoryLocation::create([
                'store_id' => $po->store_id,
                'name' => $store->name.' - Main',
                'location_type' => 'warehouse',
                'status' => 'active',
            ]);
        }
        $location = InventoryLocation::query()->lockForUpdate()->findOrFail($location->id);

        foreach ($items as $item) {
            $receivedQty = $received[$item->id] ?? 0;
            if ($receivedQty <= 0) {
                continue;
            }

            $stock = InventoryStock::query()
                ->where('location_id', $location->id)
                ->where('variant_id', $item->variant_id)
                ->whereNull('variant_option_id')
                ->lockForUpdate()
                ->first();
            if (! $stock) {
                $stock = new InventoryStock([
                    'location_id' => $location->id,
                    'variant_id' => $item->variant_id,
                    'variant_option_id' => null,
                    'quantity_on_hand' => 0,
                    'quantity_reserved' => 0,
                    'reorder_point' => 5,
                ]);
            }
            $stock->quantity_on_hand += $receivedQty;
            $stock->save();

            $item->received_quantity = (int) $item->received_quantity + $receivedQty;
            $item->save();

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

        $fullyReceived = $items->every(fn ($item) => (int) $item->received_quantity >= (int) $item->quantity);
        $status = $fullyReceived ? 'received' : 'partially_received';
        $po->update([
            'status' => $status,
            'received_date' => $fullyReceived ? now()->toDateString() : null,
        ]);

        return ['received_units' => $receivedUnits, 'status' => $status];
    }

    /**
     * Search products by name or SKU for the PO form
     */
    public function searchProducts(Request $request): array
    {
        $search = trim((string) $request->get('q', ''));
        $storeId = (int) $request->get('store_id', 0);
        $currentStoreId = CurrentStore::id();
        if (! $storeId || ($currentStoreId !== null && $storeId !== $currentStoreId)) {
            return ['results' => []];
        }

        $products = Product::query()
            ->where(fn ($query) => $query->whereNull('store_id')->orWhere('store_id', $storeId))
            ->where('status', 'active')
            ->whereHas('variants', fn ($query) => $query->where('status', 'active'))
            ->when($search !== '', fn ($query) => $query->where(function ($filter) use ($search) {
                $filter->where('name', 'like', '%'.$search.'%')
                    ->orWhereHas('variants', fn ($variants) => $variants->where('status', 'active')->where('sku', 'like', '%'.$search.'%'));
            }))
            ->with(['variants' => fn ($query) => $query->select('id', 'product_id', 'name', 'sku', 'sale_price', 'cost_price')->where('status', 'active')->orderBy('name')])
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name']);

        return [
            'results' => $products->map(fn ($product) => [
                'id' => $product->id,
                'text' => $product->name,
                'product_name' => $product->name,
                'variant_count' => $product->variants->count(),
                'variants' => $product->variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                    'cost_price' => (float) $variant->cost_price,
                    'sale_price' => (float) $variant->sale_price,
                ])->values(),
            ])->values(),
        ];
    }
}
