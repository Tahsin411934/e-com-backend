<?php

namespace Modules\Store\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Store\Models\Store;
use Yajra\DataTables\DataTables;

class StoreService
{
    public function getStoreDataTable(Request $request)
    {
        $query = Store::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('status', function (Store $store) {
                return ucfirst($store->status);
            })
            ->editColumn('created_at', function (Store $store) {
                return $store->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Store $store) {
                return view('components.action-buttons', [
                    'id' => $store->id,
                    'edit' => 'storeEdit',
                    'delete' => 'storeDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveStore(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $storeId = $data['store_id'] ?? null;

                if (! isset($data['slug']) && isset($data['name'])) {
                    $data['slug'] = Str::slug($data['name']);
                }

                unset($data['store_id']);

                if ($storeId) {
                    $store = Store::findOrFail($storeId);
                    $store->update($data);
                    $message = 'Store updated successfully.';
                } else {
                    $store = Store::create($data);
                    $message = 'Store created successfully.';
                }

                return ApiResponse::success($store->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving store: '.$e->getMessage(), 500);
        }
    }

    public function getStoreById(int $id): JsonResponse
    {
        try {
            $store = Store::findOrFail($id);

            return ApiResponse::success($store);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Store not found.');
        }
    }

    public function deleteStore(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $store = Store::findOrFail($id);
                $store->delete();

                return ApiResponse::success(null, 'Store deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting store: '.$e->getMessage(), 500);
        }
    }

    public function getAllActiveStores(): array
    {
        return Store::where('status', 'active')->orderBy('name')->get()->toArray();
    }
}
