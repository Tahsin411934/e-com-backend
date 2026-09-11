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
    public function __construct(private StoreRegistrationService $registrationService) {}

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
                'permission' => 'stores',
                'entityLabel' => 'Store',
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
                    // Owner credentials are only set when the store is created.
                    $this->forgetOwnerFields($data);

                    $store = Store::findOrFail($storeId);
                    $store->update($data);
                    $message = 'Store updated successfully.';
                } else {
                    // A new store always creates its owner's login credentials
                    // (same as public registration) and links them as the owner.
                    $owner = $this->registrationService->createOwnerUser([
                        'first_name' => $data['owner_first_name'],
                        'last_name' => $data['owner_last_name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'] ?? null,
                        'password' => $data['password'],
                    ]);

                    $this->forgetOwnerFields($data);

                    $data['owner_id'] = $owner->id;

                    $store = Store::create($data);
                    $message = 'Store created successfully. Owner login: '.$owner->email;
                }

                return ApiResponse::success($store->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving store: '.$e->getMessage(), 500);
        }
    }

    /**
     * Owner credential inputs are never stored on the store itself.
     */
    private function forgetOwnerFields(array &$data): void
    {
        unset($data['owner_first_name'], $data['owner_last_name'], $data['password'], $data['password_confirmation']);
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
