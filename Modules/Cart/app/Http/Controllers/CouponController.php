<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Cart\Http\Requests\CouponRequest;
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

    public function store(CouponRequest $request)
    {
        return $this->couponService->saveCoupon($request->validated());
    }

    public function show($id)
    {
        return $this->couponService->getCouponById((int) $id);
    }

    public function update(CouponRequest $request, int $id)
    {
        return $this->couponService->saveCoupon($request->validated() + ['coupon_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->couponService->deleteCoupon((int) $id);
    }
}
