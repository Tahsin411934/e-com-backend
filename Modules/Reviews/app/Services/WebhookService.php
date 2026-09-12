<?php

namespace Modules\Reviews\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Reviews\Models\Webhook;
use Yajra\DataTables\DataTables;

class WebhookService
{
    public function getWebhookDataTable(Request $request)
    {
        $query = Webhook::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($request->filled('status')) {
                    $query->where('status', (string) $request->string('status'));
                }
            })
            ->editColumn('url', fn ($w) => '<span class="text-xs break-all">'.e(Str::limit($w->url ?? '', 60)).'</span>')
            ->editColumn('events', function ($w) {
                $events = collect($w->events ?? []);

                return $events->isNotEmpty()
                    ? $events->map(fn ($event) => '<span class="px-1.5 py-0.5 rounded text-[11px] font-medium bg-indigo-100 text-indigo-700 mr-1">'.e($event).'</span>')->implode('')
                    : '<span class="text-gray-400 text-xs">All events</span>';
            })
            ->editColumn('status', function ($w) {
                $colors = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-gray-100 text-gray-600', 'failed' => 'bg-red-100 text-red-700'];
                $color = $colors[$w->status] ?? 'bg-gray-100 text-gray-700';

                return '<span class="px-2 py-0.5 rounded-full text-xs font-semibold '.$color.'">'.e(ucfirst($w->status)).'</span>';
            })
            ->editColumn('created_at', fn ($w) => $w->created_at?->format('d M Y H:i'))
            ->addColumn('action', fn ($w) => view('components.action-buttons', ['id' => $w->id, 'edit' => 'webhookEdit', 'delete' => 'webhookDelete'])->render())
            ->rawColumns(['action', 'status', 'events', 'url'])->make(true);
    }

    public function saveWebhook(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $id = $data['webhook_id'] ?? null;
                unset($data['webhook_id']);

                if ($id) {
                    $item = Webhook::findOrFail($id);
                    $item->update($data);

                    return ApiResponse::success($item->fresh(), 'Webhook updated.');
                }

                $item = Webhook::create($data);

                return ApiResponse::created($item->fresh(), 'Webhook created.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error: '.$e->getMessage(), 500);
        }
    }

    public function getWebhookById(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(Webhook::with('deliveries')->findOrFail($id));
        } catch (\Exception) {
            return ApiResponse::notFound('Webhook not found.');
        }
    }

    public function deleteWebhook(int $id): JsonResponse
    {
        try {
            Webhook::findOrFail($id)->delete();

            return ApiResponse::success(null, 'Webhook deleted.');
        } catch (\Exception $e) {
            return ApiResponse::error('Error: '.$e->getMessage(), 500);
        }
    }
}
