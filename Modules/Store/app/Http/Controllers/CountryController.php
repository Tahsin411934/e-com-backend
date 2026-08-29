<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Store\Http\Requests\CountryRequest;
use Modules\Store\Services\CountryService;

class CountryController extends Controller
{
    public function __construct(private CountryService $countryService) {}

    public function index()
    {
        return view('store::countries.index');
    }

    public function dataTable(Request $request)
    {
        return $this->countryService->getCountryDataTable($request);
    }

    public function store(CountryRequest $request)
    {
        return $this->countryService->saveCountry($request->validated());
    }

    public function show($id)
    {
        return $this->countryService->getCountryById((int) $id);
    }

    public function update(CountryRequest $request, $id)
    {
        return $this->countryService->saveCountry($request->validated() + ['country_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->countryService->deleteCountry((int) $id);
    }
}
