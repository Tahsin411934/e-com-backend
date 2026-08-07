<?php

namespace Modules\Cart\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Cart\Models\Coupon;
use Yajra\DataTables\DataTables;

class CouponService
{
    public function getCouponDataTable(Request $request)
    {
        $query = Coupon::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('status', function (Coupon $coupon) {
                return ucfirst($coupon->status);
            })
            ->editColumn('discount_type', function (Coupon $coupon) {
                return str_replace('_', ' ', ucfirst($coupon->discount_type));
            })
            ->editColumn('created_at', function (Coupon $coupon) {
                return $coupon->created_at->format('d M Y H:i');
            })
            ->editColumn('starts_at', function (Coupon $coupon) {
                return $coupon->starts_at ? $coupon->starts_at->format('d M Y H:i') : '-';
            })
            ->editColumn('ends_at', function (Coupon $coupon) {
                return $coupon->ends_at ? $coupon->ends_at->format('d M Y H:i') : '-';
            })
            ->addColumn('action', function (Coupon $coupon) {
                return view('components.action-buttons', [
                    'id' => $coupon->id,
                    'edit' => 'couponEdit',
                    'delete' => 'couponDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveCoupon(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $couponId = $data['coupon_id'] ?? null;

                if (!isset($data['code']) && isset($data['name'])) {
                    $data['code'] = strtoupper(Str::slug($data['name'], '-'));
                }

                unset($data['coupon_id']);

                if ($couponId) {
                    $coupon = Coupon::findOrFail($couponId);
                    $coupon->update($data);
                    $message = 'Coupon updated successfully.';
                } else {
                    $coupon = Coupon::create($data);
                    $message = 'Coupon created successfully.';
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'coupon' => $coupon->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving coupon: ' . $e->getMessage(),
            ];
        }
    }

    public function getCouponById(int $id): array
    {
        try {
            $coupon = Coupon::findOrFail($id);
            return [
                'status' => 'success',
                'coupon' => $coupon,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Coupon not found.',
            ];
        }
    }

    public function deleteCoupon(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $coupon = Coupon::findOrFail($id);
                $coupon->delete();

                return [
                    'status' => 'success',
                    'message' => 'Coupon deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting coupon: ' . $e->getMessage(),
            ];
        }
    }
}