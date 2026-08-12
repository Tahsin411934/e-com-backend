<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Account\Services\AccountReportService;

class AccountReportController extends Controller
{
    public function __construct(private AccountReportService $service) {}

    public function transactions()
    {
        return view('account::reports.transactions');
    }

    public function transactionsDataTable(Request $request)
    {
        return $this->service->transactionsDataTable($request);
    }

    public function productProfits()
    {
        return view('account::reports.product-profits');
    }

    public function productProfitsDataTable(Request $request)
    {
        return $this->service->productProfitDataTable($request);
    }
}
