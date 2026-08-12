<?php

namespace Modules\Account\Services;

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

    public function save(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                if (!empty($data['transfer_id'])) {
                    return ['status' => 'error', 'message' => 'Posted transfers cannot be edited.'];
                }

                $from = AccountAccount::findOrFail($data['from_account_id']);
                $to = AccountAccount::findOrFail($data['to_account_id']);
                $amountOut = (float) $data['amount'] + (float) ($data['transfer_fee'] ?? 0);

                if ((float) $from->current_balance < $amountOut) {
                    return ['status' => 'error', 'message' => 'Insufficient balance in source account.'];
                }

                $data['transfer_no'] = $this->generateTransferNo();
                $data['currency_code'] = strtoupper($data['currency_code'] ?? $from->currency_code);
                $data['transfer_fee'] = $data['transfer_fee'] ?? 0;
                $data['created_by'] = auth()->id();
                $transfer = AccountTransfer::create($data);
                $transaction = $this->transactionService->postTransfer($transfer, $from, $to);
                $transfer->update(['transaction_id' => $transaction->id]);

                return ['status' => 'success', 'message' => 'Transfer posted successfully.', 'transfer' => $transfer->fresh(['fromAccount', 'toAccount'])];
            });
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Error saving transfer: ' . $e->getMessage()];
        }
    }

    private function generateTransferNo(): string
    {
        do {
            $number = 'TRF-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
        } while (AccountTransfer::where('transfer_no', $number)->exists());

        return $number;
    }
}
