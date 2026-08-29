<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Account\Http\Requests\AccountCategoryRequest;
use Modules\Account\Services\AccountCategoryService;

class AccountCategoryController extends Controller
{
    public function __construct(private AccountCategoryService $service) {}

    public function index()
    {
        return view('account::categories.index', [
            'parentCategories' => $this->service->activeOptions(),
        ]);
    }

    public function dataTable(Request $request)
    {
        return $this->service->getDataTable($request);
    }

    public function store(AccountCategoryRequest $request)
    {
        return $this->service->save($request->validated());
    }

    public function show($id)
    {
        return $this->service->find((int) $id);
    }

    public function update(AccountCategoryRequest $request, $id)
    {
        return $this->service->save($request->validated() + ['category_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->service->delete((int) $id);
    }
}
