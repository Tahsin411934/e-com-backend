<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Http\Requests\StoreTaxRateRequest;
use Modules\Catalog\Services\TaxRateService;

class TaxRateController extends Controller
{
    public function __construct(private TaxRateService $taxRateService) {}

    public function index(Request $request)
    {
        return view('catalog::tax-rates');
    }

    public function dataTable(Request $request)
    {
        return $this->taxRateService->getTaxRateDataTable($request);
    }

    public function store(StoreTaxRateRequest $request)
    {
        return $this->taxRateService->saveTaxRate($request->validated());
    }

    public function show($id)
    {
        return $this->taxRateService->getTaxRateById((int) $id);
    }

    public function update(StoreTaxRateRequest $request, $id)
    {
        return $this->taxRateService->saveTaxRate($request->validated() + ['tax_rate_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->taxRateService->deleteTaxRate((int) $id);
    }
}
