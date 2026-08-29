<?php

namespace Modules\Account\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Account\Models\AccountAccount;
use Modules\Account\Models\AccountTransfer;
use Yajra\DataTables\DataTables;

class AccountTransferService
{
    public function __construct(private AccountTransactionService $transactionService) {}

    public function getDataTable(Request $request)
    {
        $query = AccountTransfer::with(['fromAccount', 'toAccount'])->orderByDesc('transferred_at')->orderByDesc('id');

        return DataTables::of($query)
            ->addColumn('from_account', fn (AccountTransfer $transfer) => $transfer->fromAccount?->name ?? '-')
            ->addColumn('to_account', fn (AccountTransfer $transfer) => $transfer->toAccount?->name ?? '-')
            ->editColumn('amount', fn (AccountTransfer $transfer) => number_format((float) $transfer->amount, 2))
            ->editColumn('transfer_fee', fn (AccountTransfer $transfer) => number_format((float) $transfer->transfer_fee, 2))
            ->editColumn('transferred_at', fn (AccountTransfer $transfer) => $transfer->transferred_at?->format('d M Y H:i') ?? '-')
            ->addColumn('action', fn () => '<span class="text-xs text-gray-400">Posted</span>')
            ->rawColumns(['action'])
            ->make(true);
    }

    public function save(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                if (! empty($data['transfer_id'])) {
                    return ApiResponse::error('Posted transfers cannot be edited.', 500);
                }

                $from = AccountAccount::findOrFail($data['from_account_id']);
                $to = AccountAccount::findOrFail($data['to_account_id']);
                $amountOut = (float) $data['amount'] + (float) ($data['transfer_fee'] ?? 0);

                if ((float) $from->current_balance < $amountOut) {
                    return ApiResponse::error('Insufficient balance in source account.', 500);
                }

                $data['transfer_no'] = $this->generateTransferNo();
                $data['currency_code'] = strtoupper($data['currency_code'] ?? $from->currency_code);
                $data['transfer_fee'] = $data['transfer_fee'] ?? 0;
                $data['created_by'] = auth()->id();
                $transfer = AccountTransfer::create($data);
                $transaction = $this->transactionService->postTransfer($transfer, $from, $to);
                $transfer->update(['transaction_id' => $transaction->id]);

                return ApiResponse::created($transfer->fresh(['fromAccount', 'toAccount']), 'Transfer posted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving transfer: '.$e->getMessage(), 500);
        }
    }

    private function generateTransferNo(): string
    {
        do {
            $number = 'TRF-'.now()->format('YmdHis').'-'.strtoupper(Str::random(4));
        } while (AccountTransfer::where('transfer_no', $number)->exists());

        return $number;
    }
}
