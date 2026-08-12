<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Account\Services\AccountDashboardService;

class AccountController extends Controller
{
    public function __construct(private AccountDashboardService $dashboardService) {}

    public function index()
    {
        $data = $this->dashboardService->dashboard(request());

        return view('account::dashboard', $data);
    }
}
