<?php

namespace Modules\Catalog\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Size;
use Modules\Catalog\Models\SizeGroup;
use Yajra\DataTables\DataTables;

class SizeService
{
    public function getSizeDataTable(Request $request)
    {
        $query = Size::query()->with('sizeGroup')->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('sizes', function (Size $size) {
                $items = array_map('trim', explode(',', $size->sizes));
                $badges = '';
                foreach ($items as $item) {
                    if (! empty($item)) {
                        $badges .= '<span class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded-full mr-1 mb-1">'.e($item).'</span>';
                    }
                }

                return $badges;
            })
            ->editColumn('status', function (Size $size) {
                return ucfirst($size->status);
            })
            ->editColumn('created_at', function (Size $size) {
                return $size->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Size $size) {
                return view('components.action-buttons', [
                'permission' => 'sizes',
                'entityLabel' => 'Size',
                    'id' => $size->id,
                    'edit' => 'sizeEdit',
                    'delete' => 'sizeDelete',
                ])->render();
            })
            ->rawColumns(['sizes', 'action'])
            ->make(true);
    }

    public function saveSize(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $sizeId = $data['size_id'] ?? null;
                $data['status'] = $data['status'] ?? 'active';

                // Clean sizes - split by comma, trim, remove empty, rejoin
                if (isset($data['sizes']) && is_string($data['sizes'])) {
                    $items = array_map('trim', explode(',', $data['sizes']));
                    $items = array_filter($items, function ($v) {
                        return ! empty($v);
                    });
                    $data['sizes'] = implode(', ', $items);
                }

                // Each size group may hold exactly ONE size set.
                $takenBy = Size::where('size_group_id', $data['size_group_id'] ?? 0)
                    ->when($sizeId, fn ($q) => $q->where('id', '!=', $sizeId))
                    ->first();
                if ($takenBy) {
                    return ApiResponse::error('This size group already has a size set.', 409);
                }

                // Denormalized group_name stays in sync with the group table
                if (! empty($data['size_group_id'])) {
                    $data['group_name'] = SizeGroup::find($data['size_group_id'])?->name ?? ($data['group_name'] ?? null);
                }

                unset($data['size_id']);

                if ($sizeId) {
                    $size = Size::findOrFail($sizeId);
                    $size->update($data);

                    return ApiResponse::success($size->fresh(), 'Size updated successfully.');
                }

                $size = Size::create($data);

                return ApiResponse::created($size->fresh(), 'Size created successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving size group: '.$e->getMessage(), 500);
        }
    }

    public function getSizeById(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(Size::findOrFail($id));
        } catch (\Exception) {
            return ApiResponse::notFound('Size not found.');
        }
    }

    /**
     * Size set attached to a size group (null when the group has none yet).
     */
    public function getSizeByGroupId(int $sizeGroupId): JsonResponse
    {
        $size = Size::where('size_group_id', $sizeGroupId)->first();

        return ApiResponse::success($size);
    }

    public function deleteSize(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $size = Size::findOrFail($id);
                $size->delete();

                return ApiResponse::success(null, 'Size deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting size group: '.$e->getMessage(), 500);
        }
    }
}
