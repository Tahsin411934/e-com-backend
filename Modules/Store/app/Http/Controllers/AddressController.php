<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Store\Http\Requests\AddressRequest;
use Modules\Store\Services\AddressService;
use Modules\Store\Services\CountryService;
use Modules\Store\Services\StoreService;

class AddressController extends Controller
{
    public function __construct(
        private AddressService $addressService,
        private CountryService $countryService,
        private StoreService $storeService
    ) {}

    public function index()
    {
        $countries = $this->countryService->getAllCountries();
        $stores = $this->storeService->getAllActiveStores();

        return view('store::addresses.index', compact('countries', 'stores'));
    }

    public function dataTable(Request $request)
    {
        return $this->addressService->getAddressDataTable($request);
    }

    public function store(AddressRequest $request)
    {
        return $this->addressService->saveAddress($request->validated());
    }

    public function show($id)
    {
        return $this->addressService->getAddressById((int) $id);
    }

    public function update(AddressRequest $request, $id)
    {
        return $this->addressService->saveAddress($request->validated() + ['address_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->addressService->deleteAddress((int) $id);
    }
}
