<?php

namespace Modules\Reviews\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Reviews\Models\AuditLog;
use Yajra\DataTables\DataTables;

class AuditLogService
{
    public function getAuditLogDataTable(Request $request)
    {
        $query = AuditLog::query()->with('user')->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('user_name', fn ($l) => $l->user ? $l->user->name : 'System')
            ->editColumn('created_at', fn ($l) => $l->created_at->format('d M Y H:i'))
            ->addColumn('action', fn ($l) => view('components.action-buttons', ['id' => $l->id, 'view' => 'auditLogView', 'delete' => 'auditLogDelete'])->render())
            ->rawColumns(['action'])->make(true);
    }

    public function getAuditLogById(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(AuditLog::with('user')->findOrFail($id));
        } catch (\Exception) {
            return ApiResponse::notFound('Log not found.');
        }
    }

    public function deleteAuditLog(int $id): JsonResponse
    {
        try {
            AuditLog::findOrFail($id)->delete();

            return ApiResponse::success(null, 'Log deleted.');
        } catch (\Exception $e) {
            return ApiResponse::error('Error: '.$e->getMessage(), 500);
        }
    }
}
