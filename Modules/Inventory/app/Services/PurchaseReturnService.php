<?php

namespace Modules\Inventory\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Inventory\Models\PurchaseReturn;
use Modules\Inventory\Models\Supplier;
use Modules\Store\Models\Store;
use Yajra\DataTables\DataTables;

class PurchaseReturnService
{
    public function getDataTable(Request $request)
    {
        $query = PurchaseReturn::with(['supplier', 'store', 'purchaseOrder'])
            ->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('status', function (PurchaseReturn $return) {
                $colors = ['draft' => 'gray', 'returned' => 'orange', 'partially_refunded' => 'yellow', 'refunded' => 'green', 'cancelled' => 'red'];
                $color = $colors[$return->status] ?? 'gray';

                return '<span class="px-2 py-1 text-xs font-medium rounded-full bg-'.$color.'-100 text-'.$color.'-800">'.ucfirst(str_replace('_', ' ', $return->status)).'</span>';
            })
            ->editColumn('refund_status', function (PurchaseReturn $return) {
                return ucfirst($return->refund_status);
            })
            ->editColumn('total_refund_amount', function (PurchaseReturn $return) {
                return number_format($return->total_refund_amount, 2);
            })
            ->editColumn('return_date', function (PurchaseReturn $return) {
                return $return->return_date->format('d M Y');
            })
            ->editColumn('created_at', function (PurchaseReturn $return) {
                return $return->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (PurchaseReturn $return) {
                return view('components.action-buttons', [
                'permission' => 'purchase-returns',
                'entityLabel' => 'Purchase Return',
                    'id' => $return->id,
                    'edit' => 'purchaseReturnEdit',
                    'delete' => 'purchaseReturnDelete',
                ])->render();
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function saveReturn(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $returnId = $data['return_id'] ?? null;
                $data['return_date'] = $data['return_date'] ?? now()->toDateString();
                unset($data['return_id']);

                if ($returnId) {
                    $return = PurchaseReturn::findOrFail($returnId);
                    $return->update($data);
                    $message = 'Purchase return updated successfully.';
                } else {
                    $data['return_number'] = PurchaseReturn::generateReturnNumber();
                    $data['created_by'] = Auth::id();
                    $return = PurchaseReturn::create($data);
                    $message = 'Purchase return created successfully.';
                }

                // Reflect a completed return back on the linked purchase order.
                if ($return->purchaseOrder) {
                    if (in_array($return->status, ['returned', 'refunded'], true)) {
                        $return->purchaseOrder->update(['status' => 'returned']);
                    } elseif ($return->status === 'partially_refunded') {
                        $return->purchaseOrder->update(['status' => 'partially_received']);
                    }
                }

                // If status is 'returned', adjust stock (decrease stock)
                if ($return->status === 'returned') {
                    $this->adjustStockForReturn($return);
                }

                if ($returnId) {
                    return ApiResponse::success($return->fresh()->load(['supplier', 'store', 'purchaseOrder']), $message);
                }

                return ApiResponse::created($return->fresh()->load(['supplier', 'store', 'purchaseOrder']), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving purchase return: '.$e->getMessage(), 500);
        }
    }

    public function getReturnById(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(PurchaseReturn::with(['supplier', 'store', 'purchaseOrder'])->findOrFail($id));
        } catch (\Exception) {
            return ApiResponse::notFound('Purchase return not found.');
        }
    }

    /**
     * Raw model lookup for edit views (findOrFail → automatic 404).
     */
    public function getReturnModel(int $id): PurchaseReturn
    {
        return PurchaseReturn::with(['supplier', 'store', 'purchaseOrder'])->findOrFail($id);
    }

    public function deleteReturn(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $return = PurchaseReturn::findOrFail($id);
                $return->delete();

                return ApiResponse::success(null, 'Purchase return deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting purchase return: '.$e->getMessage(), 500);
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
        return PurchaseOrder::orderBy('po_number')->get();
    }

    private function adjustStockForReturn(PurchaseReturn $return): void
    {
        // Log an inventory movement for the return. There is no single variant for an
        // aggregate return, so variant_id is null (the column is now nullable).
        InventoryMovement::create([
            'location_id' => $return->store_id,
            'variant_id' => null,
            'movement_type' => 'return',
            'quantity' => 0,
            'reference_type' => 'purchase_return',
            'reference_id' => $return->id,
            'note' => 'Purchase return: '.$return->return_number.' - '.($return->reason ?? ''),
            'created_by' => Auth::id(),
        ]);
    }
}
