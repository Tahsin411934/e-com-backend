<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Requests\StoreHomepageCtaRequest;
use Modules\Frontend\Http\Requests\UpdateHomepageCtaRequest;
use Modules\Frontend\Services\HomepageCtaService;

class HomepageCtaController extends Controller
{
    public function __construct(private HomepageCtaService $ctaService) {}

    public function index()
    {
        return view('frontend::homepage-ctas');
    }

    public function dataTable(Request $request)
    {
        return $this->ctaService->getCtaDataTable($request);
    }

    public function store(StoreHomepageCtaRequest $request)
    {
        return $this->ctaService->saveCta($request->validated());
    }

    public function show($id)
    {
        return $this->ctaService->getCtaById((int) $id);
    }

    public function update(UpdateHomepageCtaRequest $request, $id)
    {
        return $this->ctaService->saveCta($request->validated() + ['cta_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->ctaService->deleteCta((int) $id);
    }
}
