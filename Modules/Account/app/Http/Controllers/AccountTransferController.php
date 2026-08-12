<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Account\Http\Requests\AccountTransferRequest;
use Modules\Account\Services\AccountAccountService;
use Modules\Account\Services\AccountTransferService;

class AccountTransferController extends Controller
{
    public function __construct(
        private AccountTransferService $service,
        private AccountAccountService $accountService
    ) {}

    public function index()
    {
        return view('account::transfers.index', [
            'accounts' => $this->accountService->activeOptions(),
        ]);
    }

    public function dataTable(Request $request)
    {
        return $this->service->getDataTable($request);
    }

    public function store(AccountTransferRequest $request)
    {
        $result = $this->service->save($request->validated());
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }
}
