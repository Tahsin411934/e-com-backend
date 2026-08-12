<?php

namespace Modules\Account\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Account\Models\AccountAccount;
use Modules\Account\Models\AccountCategory;
use Modules\Account\Models\AccountExpense;
use Yajra\DataTables\DataTables;

class AccountExpenseService
{
    public function __construct(private AccountTransactionService $transactionService) {}

    public function getDataTable(Request $request)
    {
        $query = AccountExpense::with(['account', 'category'])->orderByDesc('expense_date')->orderByDesc('id');

        return DataTables::of($query)
            ->addColumn('account_name', fn (AccountExpense $expense) => $expense->account?->name ?? '-')
            ->addColumn('category_name', fn (AccountExpense $expense) => $expense->category?->name ?? '-')
            ->editColumn('amount', fn (AccountExpense $expense) => number_format((float) $expense->amount, 2))
            ->editColumn('expense_date', fn (AccountExpense $expense) => $expense->expense_date?->format('d M Y') ?? '-')
            ->addColumn('action', function (AccountExpense $expense) {
                return view('components.action-buttons', [
                    'id' => $expense->id,
                    'edit' => 'accountexpenseEdit',
                    'delete' => 'accountexpenseDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function save(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $id = $data['expense_id'] ?? null;
                unset($data['expense_id']);
                $data['currency_code'] = strtoupper($data['currency_code'] ?? 'BDT');

                if ($id) {
                    return ['status' => 'error', 'message' => 'Posted expenses cannot be edited yet. Please delete and recreate with an adjustment.'];
                }

                $data['expense_no'] = $this->generateExpenseNo();
                $data['created_by'] = auth()->id();
                $expense = AccountExpense::create($data);
                $transaction = $this->transactionService->postExpense(
                    $expense,
                    AccountAccount::findOrFail($data['account_id']),
                    AccountCategory::findOrFail($data['category_id']),
                    (float) $data['amount']
                );
                $expense->update(['transaction_id' => $transaction->id]);

                return ['status' => 'success', 'message' => 'Expense posted successfully.', 'expense' => $expense->fresh(['account', 'category'])];
            });
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Error saving expense: ' . $e->getMessage()];
        }
    }

    public function find(int $id): array
    {
        try {
            return ['status' => 'success', 'expense' => AccountExpense::findOrFail($id)];
        } catch (\Exception) {
            return ['status' => 'error', 'message' => 'Expense not found.'];
        }
    }

    public function delete(int $id): array
    {
        return ['status' => 'error', 'message' => 'Posted expenses are locked. Create an adjustment entry instead.'];
    }

    private function generateExpenseNo(): string
    {
        do {
            $number = 'EXP-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
        } while (AccountExpense::where('expense_no', $number)->exists());

        return $number;
    }
}
