<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Account\Http\Requests\AccountInvestmentRequest;
use Modules\Account\Services\AccountAccountService;
use Modules\Account\Services\AccountCategoryService;
use Modules\Account\Services\AccountInvestmentService;

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
        return $this->service->save($request->validated());
    }

    public function show($id)
    {
        return $this->service->find((int) $id);
    }

    public function update(AccountInvestmentRequest $request, $id)
    {
        return $this->service->save($request->validated() + ['investment_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->service->delete((int) $id);
    }
}
