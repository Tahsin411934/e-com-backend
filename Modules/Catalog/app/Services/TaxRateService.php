<?php

namespace Modules\Catalog\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\TaxRate;
use Yajra\DataTables\DataTables;

class TaxRateService
{
    public function getTaxRateDataTable(Request $request)
    {
        $query = TaxRate::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('rate', function (TaxRate $tax) {
                if ($tax->type === 'percentage') {
                    return $tax->rate . '%';
                }
                return '৳' . number_format($tax->rate, 2);
            })
            ->editColumn('type', function (TaxRate $tax) {
                $badge = $tax->type === 'percentage' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800';
                return '<span class="inline-block ' . $badge . ' text-xs font-medium px-2.5 py-1 rounded-full">' . ucfirst($tax->type) . '</span>';
            })
            ->editColumn('is_default', function (TaxRate $tax) {
                return $tax->is_default
                    ? '<span class="inline-block bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full">Default</span>'
                    : '<span class="text-gray-400 text-xs">—</span>';
            })
            ->editColumn('status', function (TaxRate $tax) {
                $badge = $tax->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                return '<span class="inline-block ' . $badge . ' text-xs font-medium px-2.5 py-1 rounded-full">' . ucfirst($tax->status) . '</span>';
            })
            ->editColumn('created_at', function (TaxRate $tax) {
                return $tax->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (TaxRate $tax) {
                return view('components.action-buttons', [
                    'id' => $tax->id,
                    'edit' => 'taxRateEdit',
                    'delete' => 'taxRateDelete',
                ])->render();
            })
            ->rawColumns(['type', 'is_default', 'status', 'action'])
            ->make(true);
    }

    public function saveTaxRate(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $taxId = $data['tax_rate_id'] ?? null;
                unset($data['tax_rate_id']);

                if ($taxId) {
                    $tax = TaxRate::findOrFail($taxId);
                    $tax->update($data);
                    $message = 'Tax rate updated successfully.';
                } else {
                    $tax = TaxRate::create($data);
                    $message = 'Tax rate created successfully.';
                }

                // If set as default, unset others
                if (!empty($data['is_default'])) {
                    TaxRate::where('id', '!=', $tax->id)->update(['is_default' => false]);
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'tax_rate' => $tax->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving tax rate: ' . $e->getMessage(),
            ];
        }
    }

    public function getTaxRateById(int $id): array
    {
        try {
            $tax = TaxRate::findOrFail($id);
            return ['status' => 'success', 'tax_rate' => $tax];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Tax rate not found.'];
        }
    }

    public function deleteTaxRate(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $tax = TaxRate::findOrFail($id);
                $tax->delete();
                return ['status' => 'success', 'message' => 'Tax rate deleted successfully.'];
            });
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Error deleting tax rate: ' . $e->getMessage()];
        }
    }

    public function getActiveTaxRates()
    {
        return TaxRate::where('status', 'active')->orderBy('name')->get();
    }
}