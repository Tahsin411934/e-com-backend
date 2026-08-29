<?php

namespace Modules\Store\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Store\Models\Address;
use Yajra\DataTables\DataTables;

class AddressService
{
    public function getAddressDataTable(Request $request)
    {
        $query = Address::with(['user', 'store', 'country'])->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('user_name', function (Address $address) {
                return $address->user?->first_name.' '.$address->user?->last_name ?? '-';
            })
            ->addColumn('store_name', function (Address $address) {
                return $address->store?->name ?? '-';
            })
            ->addColumn('country_name', function (Address $address) {
                return $address->country?->name ?? '-';
            })
            ->editColumn('is_default', function (Address $address) {
                return $address->is_default ? '<span class="text-green-600 font-medium">Yes</span>' : 'No';
            })
            ->editColumn('created_at', function (Address $address) {
                return $address->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Address $address) {
                return view('components.action-buttons', [
                    'id' => $address->id,
                    'edit' => 'addressEdit',
                    'delete' => 'addressDelete',
                ])->render();
            })
            ->rawColumns(['is_default', 'action'])
            ->make(true);
    }

    public function saveAddress(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $addressId = $data['address_id'] ?? null;
                unset($data['address_id']);

                if ($addressId) {
                    $address = Address::findOrFail($addressId);
                    $address->update($data);
                    $message = 'Address updated successfully.';
                } else {
                    $address = Address::create($data);
                    $message = 'Address created successfully.';
                }

                return ApiResponse::success($address->fresh(['user', 'store', 'country']), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving address: '.$e->getMessage(), 500);
        }
    }

    public function getAddressById(int $id): JsonResponse
    {
        try {
            $address = Address::with(['user', 'store', 'country'])->findOrFail($id);

            return ApiResponse::success($address);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Address not found.');
        }
    }

    public function deleteAddress(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $address = Address::findOrFail($id);
                $address->delete();

                return ApiResponse::success(null, 'Address deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting address: '.$e->getMessage(), 500);
        }
    }
}
