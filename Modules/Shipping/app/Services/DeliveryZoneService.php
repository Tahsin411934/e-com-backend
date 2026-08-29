<?php

namespace Modules\Shipping\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Shipping\Models\DeliveryZone;
use Yajra\DataTables\DataTables;

class DeliveryZoneService
{
    public function getZoneDataTable(Request $request)
    {
        $query = DeliveryZone::with(['store', 'country'])->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('store_name', fn (DeliveryZone $zone) => $zone->store?->name ?? '-')
            ->addColumn('country_name', fn (DeliveryZone $zone) => $zone->country?->name ?? '-')
            ->editColumn('status', fn (DeliveryZone $zone) => ucfirst($zone->status))
            ->editColumn('base_fee', fn (DeliveryZone $zone) => number_format($zone->base_fee, 2))
            ->editColumn('per_km_fee', fn (DeliveryZone $zone) => number_format($zone->per_km_fee, 2))
            ->addColumn('eta_window', fn (DeliveryZone $zone) => $zone->estimated_min_days.'-'.$zone->estimated_max_days.' days')
            ->editColumn('created_at', fn (DeliveryZone $zone) => $zone->created_at->format('d M Y H:i'))
            ->addColumn('action', function (DeliveryZone $zone) {
                return view('components.action-buttons', [
                    'id' => $zone->id,
                    'edit' => 'delivery-zoneEdit',
                    'delete' => 'delivery-zoneDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveZone(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $zoneId = $data['zone_id'] ?? null;
                unset($data['zone_id']);
                $data['postal_codes'] = $this->normalizePostalCodes($data['postal_codes'] ?? null);

                if ($zoneId) {
                    $zone = DeliveryZone::findOrFail($zoneId);
                    $zone->update($data);
                    $message = 'Delivery zone updated successfully.';
                } else {
                    $zone = DeliveryZone::create($data);
                    $message = 'Delivery zone created successfully.';
                }

                if ($zoneId) {
                    return ApiResponse::success($zone->fresh()->load(['store', 'country']), $message);
                }

                return ApiResponse::created($zone->fresh()->load(['store', 'country']), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving delivery zone: '.$e->getMessage(), 500);
        }
    }

    public function getZoneById(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(DeliveryZone::with(['store', 'country'])->findOrFail($id));
        } catch (\Exception) {
            return ApiResponse::notFound('Delivery zone not found.');
        }
    }

    public function deleteZone(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                DeliveryZone::findOrFail($id)->delete();

                return ApiResponse::success(null, 'Delivery zone deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting delivery zone: '.$e->getMessage(), 500);
        }
    }

    protected function normalizePostalCodes(?string $postalCodes): ?array
    {
        if (! $postalCodes) {
            return null;
        }

        $codes = collect(explode(',', $postalCodes))
            ->map(fn ($code) => trim($code))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return empty($codes) ? null : $codes;
    }
}
