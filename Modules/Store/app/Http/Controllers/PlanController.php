<?php

namespace Modules\Store\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Modules\Store\Models\Plan;

class PlanController extends Controller
{
    public function index()
    {
        return view('store::plans.index', ['plans' => Plan::orderBy('price')->orderBy('id')->get()]);
    }

    public function publicIndex()
    {
        return ApiResponse::success(Plan::where('is_active', true)->where('is_public', true)->orderBy('price')->get());
    }
}
