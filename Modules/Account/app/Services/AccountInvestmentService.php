<?php

namespace Modules\Account\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Account\Models\AccountAccount;
use Modules\Account\Models\AccountInvestment;
use Yajra\DataTables\DataTables;

class AccountInvestmentService
{
    public function getDataTable(Request $request)
    {
        $query = AccountInvestment::with(['account'])->orderByDesc('investment_date')->orderByDesc('id');

        return DataTables::of($query)
            ->addColumn('account_name', fn (AccountInvestment $investment) => $investment->account?->name ?? '-')
            ->addColumn('investment_type', fn (AccountInvestment $investment) => ucfirst(str_replace('_', ' ', $investment->investment_type)))
            ->addColumn('amount', fn (AccountInvestment $investment) => number_format((float) $investment->amount, 2))
            ->addColumn('expected_return', fn (AccountInvestment $investment) => number_format((float) $investment->expected_return, 2))
            ->addColumn('actual_return', fn (AccountInvestment $investment) => number_format((float) $investment->actual_return, 2))
            ->addColumn('status', fn (AccountInvestment $investment) => ucfirst($investment->status))
            ->addColumn('action', function (AccountInvestment $investment) {
                return view('components.action-buttons', [
                    'id' => $investment->id,
                    'edit' => 'accountinvestmentEdit',
                    'delete' => 'accountinvestmentDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function save(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $id = $data['investment_id'] ?? null;
                unset($data['investment_id']);
                $data['currency_code'] = strtoupper($data['currency_code'] ?? 'BDT');
                $data['investment_no'] = $this->generateInvestmentNo();

                if ($id) {
                    return ['status' => 'error', 'message' => 'Posted investments cannot be edited yet. Please delete and recreate with an adjustment.'];
                }

                $data['created_by'] = auth()->id();
                $investment = AccountInvestment::create($data);

                return ['status' => 'success', 'message' => 'Investment posted successfully.', 'investment' => $investment->fresh(['account'])];
            });
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Error saving investment: ' . $e->getMessage()];
        }
    }

    public function find(int $id): array
    {
        try {
            return ['status' => 'success', 'investment' => AccountInvestment::findOrFail($id)];
        } catch (\Exception) {
            return ['status' => 'error', 'message' => 'Investment not found.'];
        }
    }

    public function delete(int $id): array
    {
        return ['status' => 'error', 'message' => 'Posted investments are locked. Create a new investment entry instead.'];
    }

    private function generateInvestmentNo(): string
    {
        do {
            $number = 'INV-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
        } while (AccountInvestment::where('investment_no', $number)->exists());

        return $number;
    }
}