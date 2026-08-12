<?php

namespace Modules\Inventory\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Account\Services\AccountTransactionService;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Inventory\Models\PurchaseOrderItem;
use Modules\Inventory\Models\InventoryStock;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\InventoryLocation;
use Modules\Catalog\Models\ProductVariant;
use Modules\Catalog\Models\Product;
use Yajra\DataTables\DataTables;

class PurchaseOrderService
{
    public function getPoDataTable(Request $request)
    {
        $query = PurchaseOrder::query()
            ->with(['supplier', 'store'])
            ->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('status', function (PurchaseOrder $po) {
                $colors = [
                    'draft' => 'bg-gray-100 text-gray-700',
                    'ordered' => 'bg-blue-100 text-blue-700',
                    'partially_received' => 'bg-yellow-100 text-yellow-700',
                    'received' => 'bg-green-100 text-green-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                ];
                $class = $colors[$po->status] ?? 'bg-gray-100';
                return '<span class="px-2 py-1 rounded-full text-xs font-medium ' . $class . '">' . ucfirst(str_replace('_', ' ', $po->status)) . '</span>';
            })
            ->editColumn('payment_status', function (PurchaseOrder $po) {
                $colors = [
                    'unpaid' => 'bg-red-100 text-red-700',
                    'partial' => 'bg-yellow-100 text-yellow-700',
                    'paid' => 'bg-green-100 text-green-700',
                ];
                $class = $colors[$po->payment_status] ?? 'bg-gray-100';
                return '<span class="px-2 py-1 rounded-full text-xs font-medium ' . $class . '">' . ucfirst(str_replace('_', ' ', $po->payment_status)) . '</span>';
            })
            ->editColumn('total_amount', function (PurchaseOrder $po) {
                return number_format($po->total_amount, 2);
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

                // Quick status workflow buttons (inline)
                if ($po->status === 'draft') {
                    $html .= '<button onclick="updatePoStatus(' . $po->id . ', \'ordered\')" class="bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700 mr-1" title="Mark as Ordered"><i class="fas fa-check"></i> Order</button>';
                }
                if (in_array($po->status, ['ordered', 'partially_received'])) {
                    $html .= '<button onclick="updatePoStatus(' . $po->id . ', \'received\')" class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700 mr-1" title="Mark as Received"><i class="fas fa-check-double"></i> Receive</button>';
                }
                if (in_array($po->status, ['draft', 'ordered'])) {
                    $html .= '<button onclick="updatePoStatus(' . $po->id . ', \'cancelled\')" class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600 mr-1" title="Cancel Order"><i class="fas fa-times"></i></button>';
                }
                if ($po->payment_status !== 'paid' && $po->status !== 'cancelled') {
                    $html .= '<button onclick="updatePoStatus(' . $po->id . ', \'paid\', \'payment_status\')" class="bg-indigo-600 text-white px-2 py-1 rounded text-xs hover:bg-indigo-700 mr-1" title="Mark as Paid"><i class="fas fa-dollar-sign"></i> Pay</button>';
                }

                // Standard action buttons
                $html .= view('components.action-buttons', [
                    'id' => $po->id,
                    'show' => true,
                    'showUrl' => route('purchase-orders.show', ':id'),
                    'editUrl' => route('purchase-orders.edit', ':id'),
                    'deleteUrl' => route('purchase-orders.destroy', ':id'),
                ])->render();

                return $html;
            })
            ->rawColumns(['action', 'status', 'payment_status'])
            ->make(true);
    }

    public function savePo(array $data): array
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

                // Sync items
                $po->items()->delete();
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

                return [
                    'status' => 'success',
                    'message' => $message,
                    'purchase_order' => $po->fresh()->load(['supplier', 'store', 'items.variant']),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving purchase order: ' . $e->getMessage(),
            ];
        }
    }

    public function getPoById(int $id): array
    {
        try {
            $po = PurchaseOrder::with(['supplier', 'store', 'items.variant.product', 'creator'])->findOrFail($id);
            return [
                'status' => 'success',
                'purchase_order' => $po,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Purchase order not found.',
            ];
        }
    }

    public function deletePo(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $po = PurchaseOrder::findOrFail($id);
                if ($po->status !== 'draft') {
                    return ['status' => 'error', 'message' => 'Only draft orders can be deleted.'];
                }
                $po->delete();
                return ['status' => 'success', 'message' => 'Purchase order deleted successfully.'];
            });
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Error deleting purchase order: ' . $e->getMessage()];
        }
    }

    public function updateStatus(int $id, ?string $status = null, ?string $paymentStatus = null): array
    {
        try {
            return DB::transaction(function () use ($id, $status, $paymentStatus) {
                $po = PurchaseOrder::findOrFail($id);

                if (!$status && !$paymentStatus) {
                    return ['status' => 'error', 'message' => 'Nothing to update.'];
                }

                if ($status) {
                    $validTransitions = [
                        'draft' => ['ordered', 'cancelled'],
                        'ordered' => ['partially_received', 'received', 'cancelled'],
                        'partially_received' => ['received'],
                    ];

                    if (!in_array($status, $validTransitions[$po->status] ?? [])) {
                        return ['status' => 'error', 'message' => 'Cannot change status from "' . $po->status . '" to "' . $status . '".'];
                    }

                    $po->update(['status' => $status]);

                    if ($status === 'received') {
                        $this->processStockUpdate($po);
                        $po->update(['received_date' => now()->toDateString()]);
                    }
                }

                if ($paymentStatus) {
                    $validPaymentTransitions = [
                        'unpaid' => ['partial', 'paid'],
                        'partial' => ['paid'],
                        'paid' => [],
                    ];

                    if (!isset($validPaymentTransitions[$po->payment_status]) || !in_array($paymentStatus, $validPaymentTransitions[$po->payment_status])) {
                        return ['status' => 'error', 'message' => 'Cannot change payment status from "' . $po->payment_status . '" to "' . $paymentStatus . '".'];
                    }

                    $po->update(['payment_status' => $paymentStatus]);

                    if ($paymentStatus === 'paid' && Schema::hasTable('account_transactions')) {
                        app(AccountTransactionService::class)->postPurchaseOrder($po->fresh());
                    }
                }

                $message = [];
                if ($status) {
                    $message[] = 'Status updated to "' . ucfirst(str_replace('_', ' ', $status)) . '".';
                }
                if ($paymentStatus) {
                    $message[] = 'Payment status updated to "' . ucfirst(str_replace('_', ' ', $paymentStatus)) . '".';
                }

                return [
                    'status' => 'success',
                    'message' => implode(' ', $message),
                    'purchase_order' => $po->fresh()->load(['supplier', 'store', 'items.variant']),
                ];
            });
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Error updating status: ' . $e->getMessage()];
        }
    }

    protected function processStockUpdate(PurchaseOrder $po): void
    {
        $location = InventoryLocation::where('store_id', $po->store_id)->first();

        if (!$location) {
            $location = InventoryLocation::create([
                'store_id' => $po->store_id,
                'name' => $po->store->name . ' - Main',
                'location_type' => 'warehouse',
                'status' => 'active',
            ]);
        }

        foreach ($po->items as $item) {
            $receivedQty = $item->quantity - $item->received_quantity;
            if ($receivedQty <= 0) continue;

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
                'note' => 'PO ' . $po->po_number . ' received',
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
                    'text' => $product->name . ' - ' . $variant->name . ' (' . $variant->sku . ')',
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
