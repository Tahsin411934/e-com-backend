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
        $result = $this->service->save($request->validated());
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }

    public function show($id)
    {
        return response()->json($this->service->find((int) $id));
    }

    public function update(AccountExpenseRequest $request, $id)
    {
        $data = $request->validated();
        $data['expense_id'] = $id;
        $result = $this->service->save($data);
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }

    public function destroy($id)
    {
        $result = $this->service->delete((int) $id);
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }
}
