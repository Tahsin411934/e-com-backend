<?php

namespace Modules\Pos\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\Account\Services\AccountTransactionService;
use Modules\Pos\Models\PosRegister;
use Modules\Pos\Models\PosSale;
use Modules\Pos\Models\PosShift;
use Modules\Store\Support\CurrentStore;
use Yajra\DataTables\DataTables;

class PosSaleService
{
    public function getSaleDataTable(Request $request)
    {
        $query = PosSale::query()->with(['register.store', 'shift', 'user'])->orderByDesc('created_at');
        if (($storeId = CurrentStore::id()) !== null) $query->whereHas('register', fn ($q) => $q->where('store_id', $storeId));

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

    public function saveSale(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $saleId = $data['sale_id'] ?? null;
                unset($data['sale_id']);

                if (! empty($data['register_id'])) {
                    $registerQuery = PosRegister::query()->whereKey($data['register_id']);
                    if (($storeId = CurrentStore::id()) !== null) {
                        $registerQuery->where('store_id', $storeId);
                    }

                    $register = $registerQuery->first();
                    if (! $register) {
                        throw ValidationException::withMessages([
                            'register_id' => ['The selected register does not belong to the current store.'],
                        ]);
                    }

                    if (! empty($data['shift_id']) && ! PosShift::query()
                        ->whereKey($data['shift_id'])
                        ->where('register_id', $register->id)
                        ->exists()) {
                        throw ValidationException::withMessages([
                            'shift_id' => ['The selected shift does not belong to the selected register.'],
                        ]);
                    }
                }

                if (! isset($data['receipt_number'])) {
                    $data['receipt_number'] = 'POS-'.strtoupper(uniqid());
                }

                if ($saleId) {
                    $sale = PosSale::query()->when(CurrentStore::id() !== null, fn ($q) => $q->whereHas('register', fn ($r) => $r->where('store_id', CurrentStore::id())))->findOrFail($saleId);

                    if (in_array($sale->status, ['completed', 'voided', 'refunded'], true)) {
                        throw ValidationException::withMessages([
                            'sale_id' => ['Completed, voided, or refunded sales cannot be edited.'],
                        ]);
                    }

                    $sale->update($data);
                    $message = 'Sale updated successfully.';
                } else {
                    if (in_array($data['status'] ?? 'completed', ['voided', 'refunded'], true)) {
                        throw ValidationException::withMessages([
                            'status' => ['New sales must be created as completed. Use the void or refund workflow afterward.'],
                        ]);
                    }

                    $sale = PosSale::create($data);
                    $message = 'Sale created successfully.';
                }

                if (Schema::hasTable('account_transactions')) {
                    app(AccountTransactionService::class)->postPosSale($sale->fresh(['items.product.variants', 'register.store']));
                }

                return ApiResponse::success($sale->fresh()->load(['register.store', 'shift', 'user']), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving sale: '.$e->getMessage(), 500);
        }
    }

    public function getSaleById(int $id): JsonResponse
    {
        try {
            $sale = PosSale::with(['register.store', 'shift', 'user'])->when(CurrentStore::id() !== null, fn ($q) => $q->whereHas('register', fn ($r) => $r->where('store_id', CurrentStore::id())))->findOrFail($id);

            return ApiResponse::success($sale);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Sale not found.');
        }
    }

    public function deleteSale(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $sale = PosSale::query()->when(CurrentStore::id() !== null, fn ($q) => $q->whereHas('register', fn ($r) => $r->where('store_id', CurrentStore::id())))->findOrFail($id);

                if ($sale->status !== 'voided') {
                    return ApiResponse::error('Sale cannot be deleted. Void the sale first.', 422);
                }

                return ApiResponse::error('POS sales are retained for audit and cannot be deleted.', 422);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting sale: '.$e->getMessage(), 500);
        }
    }

    public function voidSale(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $sale = PosSale::query()->when(CurrentStore::id() !== null, fn ($q) => $q->whereHas('register', fn ($r) => $r->where('store_id', CurrentStore::id())))->findOrFail($id);

                if ($sale->status === 'voided') {
                    return ApiResponse::error('Sale is already voided.', 422);
                }

                if ($sale->status === 'refunded') {
                    return ApiResponse::error('A refunded sale cannot be voided.', 422);
                }

                $sale->load(['items', 'register']);
                app(PosSellService::class)->adjustInventory(
                    $sale->items->toArray(),
                    (int) $sale->register->store_id,
                    'return',
                    (int) $sale->id,
                    1
                );

                $sale->update(['status' => 'voided']);

                if ($sale->shift) {
                    $sale->shift->decrement(
                        'cash_sales',
                        max(0, (float) $sale->cash_amount - (float) $sale->change_amount)
                    );
                    $sale->shift->decrement('card_sales', (float) $sale->card_amount);
                    $sale->shift->decrement('other_sales', (float) $sale->other_amount);
                    $sale->shift->decrement('total_sales', (float) $sale->total);
                }

                return ApiResponse::success(null, 'Sale voided successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error voiding sale: '.$e->getMessage(), 500);
        }
    }
}
