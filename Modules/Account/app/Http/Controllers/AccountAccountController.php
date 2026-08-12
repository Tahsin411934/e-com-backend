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
        $result = $this->service->save($request->validated());
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }

    public function show($id)
    {
        return response()->json($this->service->find((int) $id));
    }

    public function update(AccountAccountRequest $request, $id)
    {
        $data = $request->validated();
        $data['account_id'] = $id;
        $result = $this->service->save($data);
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }

    public function destroy($id)
    {
        $result = $this->service->delete((int) $id);
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }
}
