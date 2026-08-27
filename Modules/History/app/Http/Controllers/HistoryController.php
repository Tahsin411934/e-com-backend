<?php

namespace Modules\History\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\History\Services\HistoryService;

class HistoryController extends Controller
{
    public function __construct(private readonly HistoryService $historyService)
    {
    }

    public function page()
    {
        return view('history::index');
    }

    public function index(Request $request)
    {
        return response()->json($this->historyService->list($request));
    }

    public function show(int $id)
    {
        return response()->json($this->historyService->show($id));
    }

    public function restore(int $id)
    {
        try {
            return response()->json([
                'status' => 'success',
                'message' => 'Record restored successfully.',
                'history' => $this->historyService->restore($id),
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
