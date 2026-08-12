<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Account\Http\Requests\AccountInvestmentRequest;
use Modules\Account\Services\AccountInvestmentService;
use Modules\Account\Services\AccountAccountService;
use Modules\Account\Services\AccountCategoryService;

class AccountInvestmentController extends Controller
{
    public function __construct(
        private AccountInvestmentService $service,
        private AccountAccountService $accountService,
        private AccountCategoryService $categoryService
    ) {}

    public function index()
    {
        return view('account::investments.index', [
            'accounts' => $this->accountService->activeOptions(),
            'investmentTypes' => $this->categoryService->activeOptions('investment'),
        ]);
    }

    public function dataTable(Request $request)
    {
        return $this->service->getDataTable($request);
    }

    public function store(AccountInvestmentRequest $request)
    {
        $result = $this->service->save($request->validated());
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }

    public function show($id)
    {
        return response()->json($this->service->find((int) $id));
    }

    public function update(AccountInvestmentRequest $request, $id)
    {
        $data = $request->validated();
        $data['investment_id'] = $id;
        $result = $this->service->save($data);
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }

    public function destroy($id)
    {
        $result = $this->service->delete((int) $id);
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }
}