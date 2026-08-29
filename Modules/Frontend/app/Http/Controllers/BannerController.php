<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\StoreBannerRequest;
use Modules\Frontend\Http\Requests\UpdateBannerRequest;
use Modules\Frontend\Services\BannerService;

class BannerController extends Controller
{
    public function __construct(private BannerService $bannerService) {}

    public function index()
    {
        return view('frontend::banners');
    }

    public function dataTable(Request $request)
    {
        return $this->bannerService->getBannerDataTable($request);
    }

    public function store(StoreBannerRequest $request)
    {
        return $this->bannerService->saveBanner($request->validated());
    }

    public function show($id)
    {
        return $this->bannerService->getBannerById((int) $id);
    }

    public function update(UpdateBannerRequest $request, $id)
    {
        return $this->bannerService->saveBanner($request->validated() + ['banner_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->bannerService->deleteBanner((int) $id);
    }
}
