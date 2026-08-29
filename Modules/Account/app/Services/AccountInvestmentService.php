<?php

namespace Modules\Account\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Account\Models\AccountAccount;
use Modules\Account\Models\AccountCategory;
use Modules\Account\Models\AccountInvestment;
use Yajra\DataTables\DataTables;

class AccountInvestmentService
{
    public function __construct(private AccountTransactionService $transactionService) {}

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

    public function save(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $id = $data['investment_id'] ?? null;
                unset($data['investment_id']);
                $data['currency_code'] = strtoupper($data['currency_code'] ?? 'BDT');
                $data['investment_no'] = $this->generateInvestmentNo();

                if ($id) {
                    return ApiResponse::error('Posted investments cannot be edited yet. Please delete and recreate with an adjustment.', 500);
                }

                $data['created_by'] = auth()->id();
                $investment = AccountInvestment::create($data);

                $account = AccountAccount::findOrFail($data['account_id']);
                $category = AccountCategory::where('system_key', 'investment')->first()
                    ?? $this->transactionService->ensureCategory('investment', 'Investment', 'asset');

                $transaction = $this->transactionService->postInvestment(
                    $investment,
                    $account,
                    $category,
                    (float) $data['amount']
                );
                $investment->update(['transaction_id' => $transaction->id]);

                return ApiResponse::created(
                    $investment->fresh(['account', 'transaction']),
                    'Investment posted successfully. Money was added to the "'.$account->name.'" balance.'
                );
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving investment: '.$e->getMessage(), 500);
        }
    }

    public function find(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(AccountInvestment::with(['account', 'transaction'])->findOrFail($id));
        } catch (\Exception) {
            return ApiResponse::notFound('Investment not found.');
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $investment = AccountInvestment::findOrFail($id);

                // Only reverse the account balance when a real posted transaction exists
                // (legacy investments created before this fix have none, so they were never posted).
                if ($investment->transaction) {
                    if ($account = $investment->account) {
                        $account->decrement('current_balance', (float) $investment->amount);
                    }

                    $investment->transaction->delete();
                }

                $investment->delete();

                return ApiResponse::success(null, 'Investment deleted and its account impact was reversed.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting investment: '.$e->getMessage(), 500);
        }
    }

    private function generateInvestmentNo(): string
    {
        do {
            $number = 'INV-'.now()->format('YmdHis').'-'.strtoupper(Str::random(4));
        } while (AccountInvestment::where('investment_no', $number)->exists());

        return $number;
    }
}
