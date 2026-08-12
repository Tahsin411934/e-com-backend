<?php

namespace Modules\Pos\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Account\Services\AccountTransactionService;
use Modules\Pos\Models\PosSale;
use Yajra\DataTables\DataTables;

class PosSaleService
{
    public function getSaleDataTable(Request $request)
    {
        $query = PosSale::query()->with(['register.store', 'shift', 'user'])->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('status', function (PosSale $sale) {
                return ucfirst($sale->status);
            })
            ->editColumn('payment_status', function (PosSale $sale) {
                return ucfirst($sale->payment_status);
            })
            ->addColumn('register_name', function (PosSale $sale) {
                return $sale->register ? $sale->register->name : '-';
            })
            ->addColumn('store_name', function (PosSale $sale) {
                return $sale->register && $sale->register->store ? $sale->register->store->name : '-';
            })
            ->addColumn('user_name', function (PosSale $sale) {
                return $sale->user ? $sale->user->name : '-';
            })
            ->editColumn('total', function (PosSale $sale) {
                return number_format($sale->total, 2);
            })
            ->editColumn('subtotal', function (PosSale $sale) {
                return number_format($sale->subtotal, 2);
            })
            ->editColumn('created_at', function (PosSale $sale) {
                return $sale->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (PosSale $sale) {
                return view('components.action-buttons', [
                    'id' => $sale->id,
                    'view' => 'posSaleView',
                    'delete' => 'posSaleDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveSale(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $saleId = $data['sale_id'] ?? null;
                unset($data['sale_id']);

                if (!isset($data['receipt_number'])) {
                    $data['receipt_number'] = 'POS-' . strtoupper(uniqid());
                }

                if ($saleId) {
                    $sale = PosSale::findOrFail($saleId);
                    $sale->update($data);
                    $message = 'Sale updated successfully.';
                } else {
                    $sale = PosSale::create($data);
                    $message = 'Sale created successfully.';
                }

                if (Schema::hasTable('account_transactions')) {
                    app(AccountTransactionService::class)->postPosSale($sale->fresh(['items.product.variants', 'register.store']));
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'sale' => $sale->fresh()->load(['register.store', 'shift', 'user']),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving sale: ' . $e->getMessage(),
            ];
        }
    }

    public function getSaleById(int $id): array
    {
        try {
            $sale = PosSale::with(['register.store', 'shift', 'user'])->findOrFail($id);
            return [
                'status' => 'success',
                'sale' => $sale,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Sale not found.',
            ];
        }
    }

    public function deleteSale(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $sale = PosSale::findOrFail($id);
                $sale->delete();
                return [
                    'status' => 'success',
                    'message' => 'Sale deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting sale: ' . $e->getMessage(),
            ];
        }
    }

    public function voidSale(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $sale = PosSale::findOrFail($id);
                $sale->update(['status' => 'voided']);
                return [
                    'status' => 'success',
                    'message' => 'Sale voided successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error voiding sale: ' . $e->getMessage(),
            ];
        }
    }
}
