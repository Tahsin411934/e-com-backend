<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Frontend\Models\AnnouncementBar;
use Yajra\DataTables\DataTables;

class AnnouncementBarService
{
    public function getAnnouncementBarDataTable(Request $request)
    {
        $query = AnnouncementBar::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('left_text', function (AnnouncementBar $bar) {
                return $bar->left_text ?? '-';
            })
            ->editColumn('center_text', function (AnnouncementBar $bar) {
                return $bar->center_text ?? '-';
            })
            ->editColumn('right_text', function (AnnouncementBar $bar) {
                return $bar->right_text ?? '-';
            })
            ->editColumn('background_color', function (AnnouncementBar $bar) {
                return '<span class="inline-block w-6 h-6 rounded border" style="background-color: ' . e($bar->background_color) . '"></span> '
                    . e($bar->background_color);
            })
            ->editColumn('status', function (AnnouncementBar $bar) {
                return ucfirst($bar->status);
            })
            ->editColumn('created_at', function (AnnouncementBar $bar) {
                return $bar->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (AnnouncementBar $bar) {
                $editBtn = '<button onclick="announcement_barEdit(' . $bar->id . ')" class="bg-blue-900 text-white px-2 py-1 rounded text-sm hover:bg-blue-600 mr-2"><i class="fa fa-pencil"></i></button>';
                $deleteBtn = '<button onclick="announcement_barDelete(' . $bar->id . ')" class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600"><i class="fa fa-trash"></i></button>';
                return '<div class="flex space-x-2 justify-center">' . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['background_color', 'action'])
            ->make(true);
    }

    public function saveAnnouncementBar(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $barId = $data['announcement_bar_id'] ?? null;
                $data['sort_order'] = $data['sort_order'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['announcement_bar_id']);

                if ($barId) {
                    $bar = AnnouncementBar::findOrFail($barId);
                    $bar->update($data);
                    $message = 'Announcement bar updated successfully.';
                } else {
                    $bar = AnnouncementBar::create($data);
                    $message = 'Announcement bar created successfully.';
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'announcement_bar' => $bar->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving announcement bar: ' . $e->getMessage(),
            ];
        }
    }

    public function getAnnouncementBarById(int $id): array
    {
        try {
            $bar = AnnouncementBar::findOrFail($id);

            return [
                'status' => 'success',
                'announcement_bar' => $bar->toArray(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Announcement bar not found.',
            ];
        }
    }

    public function deleteAnnouncementBar(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $bar = AnnouncementBar::findOrFail($id);
                $bar->delete();

                return [
                    'status' => 'success',
                    'message' => 'Announcement bar deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting announcement bar: ' . $e->getMessage(),
            ];
        }
    }
}