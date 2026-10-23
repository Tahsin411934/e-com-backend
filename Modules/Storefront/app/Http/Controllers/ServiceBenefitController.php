<?php

namespace Modules\Storefront\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Modules\Frontend\Models\ServiceBenefit;
use Modules\Store\Support\CurrentStore;

class ServiceBenefitController extends Controller
{
    public function index()
    {
        $items = ServiceBenefit::where('store_id', CurrentStore::id())
            ->where('is_active', true)->orderBy('sort_order')->orderBy('id')
            ->get(['id', 'title', 'description', 'icon', 'sort_order']);

        return ApiResponse::success(['items' => $items], 'Service benefits retrieved successfully.');
    }
}
