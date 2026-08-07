<?php

namespace Modules\Catalog\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Unit;
use Yajra\DataTables\DataTables;

class UnitService
{
    public function getUnitDataTable(Request $request)
    {
        $query = Unit::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('type', function (Unit $unit) {
                return ucfirst($unit->type);
            })
            ->editColumn('status', function (Unit $unit) {
                return ucfirst($unit->status);
            })
            ->editColumn('created_at', function (Unit $unit) {
                return $unit->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Unit $unit) {
                return view('components.action-buttons', [
                    'id' => $unit->id,
                    'edit' => 'unitEdit',
                    'delete' => 'unitDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveUnit(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $unitId = $data['unit_id'] ?? null;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['unit_id']);

                if ($unitId) {
                    $unit = Unit::findOrFail($unitId);
                    $unit->update($data);
                    $message = 'Unit updated successfully.';
                } else {
                    $unit = Unit::create($data);
                    $message = 'Unit created successfully.';
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'unit' => $unit->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving unit: ' . $e->getMessage(),
            ];
        }
    }

    public function getUnitById(int $id): array
    {
        try {
            $unit = Unit::findOrFail($id);

            return [
                'status' => 'success',
                'unit' => $unit,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Unit not found.',
            ];
        }
    }

    public function deleteUnit(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $unit = Unit::findOrFail($id);
                $unit->delete();

                return [
                    'status' => 'success',
                    'message' => 'Unit deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting unit: ' . $e->getMessage(),
            ];
        }
    }
}