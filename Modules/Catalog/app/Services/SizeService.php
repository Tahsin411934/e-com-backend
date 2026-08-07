<?php

namespace Modules\Catalog\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Size;
use Yajra\DataTables\DataTables;

class SizeService
{
    public function getSizeDataTable(Request $request)
    {
        $query = Size::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('sizes', function (Size $size) {
                $items = array_map('trim', explode(',', $size->sizes));
                $badges = '';
                foreach ($items as $item) {
                    if (!empty($item)) {
                        $badges .= '<span class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-1 rounded-full mr-1 mb-1">' . e($item) . '</span>';
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
                    'id' => $size->id,
                    'edit' => 'sizeEdit',
                    'delete' => 'sizeDelete',
                ])->render();
            })
            ->rawColumns(['sizes', 'action'])
            ->make(true);
    }

    public function saveSize(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $sizeId = $data['size_id'] ?? null;
                $data['status'] = $data['status'] ?? 'active';

                // Clean sizes - split by comma, trim, remove empty, rejoin
                if (isset($data['sizes']) && is_string($data['sizes'])) {
                    $items = array_map('trim', explode(',', $data['sizes']));
                    $items = array_filter($items, function($v) { return !empty($v); });
                    $data['sizes'] = implode(', ', $items);
                }

                unset($data['size_id']);

                if ($sizeId) {
                    $size = Size::findOrFail($sizeId);
                    $size->update($data);
                    $message = 'Size group updated successfully.';
                } else {
                    $size = Size::create($data);
                    $message = 'Size group created successfully.';
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'size' => $size->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving size group: ' . $e->getMessage(),
            ];
        }
    }

    public function getSizeById(int $id): array
    {
        try {
            $size = Size::findOrFail($id);
            return ['status' => 'success', 'size' => $size];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Size group not found.'];
        }
    }

    public function deleteSize(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $size = Size::findOrFail($id);
                $size->delete();
                return ['status' => 'success', 'message' => 'Size group deleted successfully.'];
            });
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Error deleting size group: ' . $e->getMessage()];
        }
    }
}