<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Account\Http\Requests\AccountExpenseRequest;
use Modules\Account\Services\AccountAccountService;
use Modules\Account\Services\AccountCategoryService;
use Modules\Account\Services\AccountExpenseService;

class AccountExpenseController extends Controller
{
    public function __construct(
        private AccountExpenseService $service,
        private AccountAccountService $accountService,
        private AccountCategoryService $categoryService
    ) {}

    public function index()
    {
        return view('account::expenses.index', [
            'accounts' => $this->accountService->activeOptions(),
            'categories' => $this->categoryService->activeOptions('expense'),
        ]);
    }

    public function dataTable(Request $request)
    {
        return $this->service->getDataTable($request);
    }

    public function store(AccountExpenseRequest $request)
    {
        return $this->service->save($request->validated());
    }

    public function show($id)
    {
        return $this->service->find((int) $id);
    }

    public function update(AccountExpenseRequest $request, $id)
    {
        return $this->service->save($request->validated() + ['expense_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->service->delete((int) $id);
    }
}
