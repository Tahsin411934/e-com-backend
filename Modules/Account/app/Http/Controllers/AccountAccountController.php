<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Account\Http\Requests\AccountAccountRequest;
use Modules\Account\Services\AccountAccountService;

class AccountAccountController extends Controller
{
    public function __construct(private AccountAccountService $service) {}

    public function index()
    {
        return view('account::accounts.index');
    }

    public function dataTable(Request $request)
    {
        return $this->service->getDataTable($request);
    }

    public function store(AccountAccountRequest $request)
    {
        return $this->service->save($request->validated());
    }

    public function show($id)
    {
        return $this->service->find((int) $id);
    }

    public function update(AccountAccountRequest $request, $id)
    {
        return $this->service->save($request->validated() + ['account_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->service->delete((int) $id);
    }
}
