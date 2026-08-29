<?php

namespace Modules\History\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\History\Models\History;
use Modules\History\Services\HistoryService;
use Yajra\DataTables\DataTables;

class HistoryController extends Controller
{
    public function __construct(private readonly HistoryService $historyService) {}

    public function page()
    {
        // Distinct audited model classes for the "Model" filter dropdown.
        $entityTypes = History::query()
            ->select('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type')
            ->mapWithKeys(fn (string $type) => [$type => class_basename($type)]);

        return view('history::index', [
            'entityTypes' => $entityTypes,
        ]);
    }

    /**
     * DataTables endpoint powering the /histories admin page
     * (same pattern as the other /dataTable/* endpoints).
     */
    public function dataTable(Request $request)
    {
        $badgeColors = [
            'created' => 'bg-green-100 text-green-700',
            'updated' => 'bg-blue-100 text-blue-700',
            'deleted' => 'bg-red-100 text-red-700',
            'restored' => 'bg-purple-100 text-purple-700',
        ];

        return DataTables::of(History::query()->with('user'))
            ->filter(function ($query) use ($request) {
                if ($request->filled('action')) {
                    $query->where('action', (string) $request->string('action'));
                }

                if ($request->filled('entity_type')) {
                    $query->where('entity_type', (string) $request->string('entity_type'));
                }

                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date('date_from'));
                }

                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date('date_to'));
                }
            })
            ->addColumn('user_name', fn (History $history) => $history->user?->name ?? $history->user?->email ?? 'System')
            ->addColumn('entity_label', function (History $history) {
                return '<span class="font-medium text-gray-800 dark:text-gray-200">'.e(class_basename($history->entity_type)).'</span>'
                    .'<span class="text-gray-400"> #'.e($history->entity_id ?? '-').'</span>';
            })
            ->addColumn('action_badge', function (History $history) use ($badgeColors) {
                $color = $badgeColors[$history->action] ?? 'bg-gray-100 text-gray-700';

                return '<span class="px-2 py-0.5 rounded-full text-xs font-semibold '.$color.'">'.e(ucfirst($history->action)).'</span>';
            })
            ->editColumn('created_at', fn (History $history) => $history->created_at?->format('d M Y, h:i A'))
            ->addColumn('row_actions', function (History $history) {
                $html = '<div class="flex items-center justify-center gap-2">';
                $html .= '<button type="button" onclick="openHistoryDetails('.$history->id.', this)" title="View details" '
                    .'class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold '
                    .'rounded-lg hover:bg-gray-50 hover:border-gray-300 transition duration-150">'
                    .'<i class="fa fa-eye mr-1 text-primary"></i> Details</button>';

                if ($history->action === 'deleted') {
                    $html .= '<button type="button" onclick="restoreHistory('.$history->id.')" title="Restore record" '
                        .'class="inline-flex items-center px-3 py-1.5 bg-primary text-white text-xs font-semibold rounded-lg '
                        .'hover:opacity-90 transition duration-150">'
                        .'<i class="fa fa-rotate-left mr-1"></i> Restore</button>';
                }

                $html .= '</div>';

                return $html;
            })
            // Keep the payload slim (the drawer data lives in the remaining row fields).
            ->removeColumn('user')
            ->rawColumns(['action_badge', 'entity_label', 'row_actions'])
            ->make(true);
    }

    public function index(Request $request)
    {
        return $this->historyService->list($request);
    }

    public function show($id)
    {
        return $this->historyService->show((int) $id);
    }

    public function restore($id)
    {
        try {
            return ApiResponse::fromResult([
                'status' => 'success',
                'message' => 'Record restored successfully.',
                'history' => $this->historyService->restore((int) $id),
            ]);
        } catch (\Throwable $exception) {
            return ApiResponse::fromResult([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 200, 422);
        }
    }
}
