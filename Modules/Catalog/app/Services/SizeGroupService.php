<?php

namespace Modules\Catalog\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Size;
use Modules\Catalog\Models\SizeGroup;
use Yajra\DataTables\DataTables;

class SizeGroupService
{
    public function getSizeGroupDataTable(Request $request)
    {
        $query = SizeGroup::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('status', function (SizeGroup $group) {
                return ucfirst($group->status);
            })
            ->editColumn('created_at', function (SizeGroup $group) {
                return $group->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (SizeGroup $group) {
                return view('components.action-buttons', [
                'permission' => 'size-groups',
                'entityLabel' => 'Size Group',
                    'id' => $group->id,
                    'edit' => 'sizeGroupEdit',
                    'delete' => 'sizeGroupDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveSizeGroup(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $groupId = $data['size_group_id'] ?? null;
                unset($data['size_group_id']);

                $data['status'] = $data['status'] ?? 'active';

                if ($groupId) {
                    $group = SizeGroup::findOrFail($groupId);
                    $oldName = $group->name;
                    $group->update($data);

                    // Keep the denormalized group_name on linked size rows in sync
                    if ($oldName !== $group->name) {
                        Size::where('size_group_id', $group->id)->update(['group_name' => $group->name]);
                    }

                    return ApiResponse::success($group->fresh(), 'Size group updated successfully.');
                }

                $group = SizeGroup::create($data);

                return ApiResponse::created($group->fresh(), 'Size group created successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving size group: '.$e->getMessage(), 500);
        }
    }

    public function getSizeGroupById(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(SizeGroup::findOrFail($id));
        } catch (\Exception) {
            return ApiResponse::notFound('Size group not found.');
        }
    }

    public function deleteSizeGroup(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $group = SizeGroup::findOrFail($id);

                // Block deletion while size rows still reference the group
                if (Size::where('size_group_id', $group->id)->exists()) {
                    return ApiResponse::error('This size group is used by one or more size sets. Remove them first.', 409);
                }

                $group->delete();

                return ApiResponse::success(null, 'Size group deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting size group: '.$e->getMessage(), 500);
        }
    }
}
