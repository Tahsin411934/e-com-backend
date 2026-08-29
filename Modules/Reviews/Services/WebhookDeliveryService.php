<?php

namespace Modules\Reviews\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Reviews\Models\WebhookDelivery;
use Yajra\DataTables\DataTables;

class WebhookDeliveryService
{
    public function getDeliveryDataTable(Request $request)
    {
        $query = WebhookDelivery::query()->with('webhook')->orderByDesc('created_at');

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($request->filled('webhook_id')) {
                    $query->where('webhook_id', $request->integer('webhook_id'));
                }

                if ($request->filled('success')) {
                    $query->where('success', $request->boolean('success'));
                }
            })
            ->addColumn('webhook_name', fn ($d) => $d->webhook ? $d->webhook->name : '-')
            ->editColumn('event', fn ($d) => '<span class="px-1.5 py-0.5 rounded text-[11px] font-medium bg-indigo-100 text-indigo-700">'.e($d->event ?? '-').'</span>')
            ->editColumn('response_status', function ($d) {
                $status = $d->response_status;
                if ($status === null) {
                    return '<span class="text-gray-400 text-xs">—</span>';
                }

                $color = $status >= 200 && $status < 300 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';

                return '<span class="px-2 py-0.5 rounded-full text-xs font-semibold '.$color.'">'.e((string) $status).'</span>';
            })
            ->editColumn('attempt', fn ($d) => '#'.(int) ($d->attempt ?? 1))
            ->editColumn('success', fn ($d) => $d->success
                ? '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Success</span>'
                : '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Failed</span>')
            ->editColumn('delivered_at', fn ($d) => $d->delivered_at ? $d->delivered_at->format('d M Y H:i') : '-')
            ->editColumn('created_at', fn ($d) => $d->created_at?->format('d M Y H:i'))
            ->addColumn('action', function ($d) {
                $html = '<div class="flex items-center justify-center gap-1">';
                $html .= '<button type="button" onclick="webhookDeliveryView('.$d->id.', this)" title="View delivery" '
                    .'class="inline-flex items-center bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-800 p-1.5 rounded text-xs transition">'
                    .'<i class="fa fa-eye"></i></button>';
                $html .= '<button type="button" onclick="webhookDeliveryDelete('.$d->id.')" title="Delete" '
                    .'class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition">'
                    .'<i class="fa fa-trash"></i></button>';
                $html .= '</div>';

                return $html;
            })
            ->rawColumns(['action', 'success', 'event', 'response_status'])->make(true);
    }

    public function getDeliveryById(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(WebhookDelivery::with('webhook')->findOrFail($id));
        } catch (\Exception) {
            return ApiResponse::notFound('Delivery not found.');
        }
    }

    public function deleteDelivery(int $id): JsonResponse
    {
        try {
            WebhookDelivery::findOrFail($id)->delete();

            return ApiResponse::success(null, 'Delivery deleted.');
        } catch (\Exception $e) {
            return ApiResponse::error('Error: '.$e->getMessage(), 500);
        }
    }
}
