<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Cart\Services\CouponService;

class CouponController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    public function index()
    {
        return view('cart::coupons.index');
    }

    public function dataTable(Request $request)
    {
        return $this->couponService->getCouponDataTable($request);
    }

    public function store(Request $request)
    {
        return $this->couponService->saveCoupon($request->all());
    }

    public function show($id)
    {
        return $this->couponService->getCouponById((int) $id);
    }

    public function update(Request $request, int $id)
    {
        return $this->couponService->saveCoupon($request->all() + ['coupon_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->couponService->deleteCoupon((int) $id);
    }
}
