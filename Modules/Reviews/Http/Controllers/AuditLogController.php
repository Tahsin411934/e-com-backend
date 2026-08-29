<?php

namespace Modules\Reviews\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Reviews\Services\AuditLogService;

class AuditLogController extends Controller
{
    public function __construct(private AuditLogService $service) {}

    public function index()
    {
        return view('reviews::audit-logs.index');
    }

    public function dataTable(Request $request)
    {
        return $this->service->getAuditLogDataTable($request);
    }

    public function show($id)
    {
        return $this->service->getAuditLogById($id);
    }

    public function destroy($id)
    {
        return $this->service->deleteAuditLog($id);
    }
}
