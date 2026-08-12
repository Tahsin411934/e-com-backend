<?php

namespace Modules\Account\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Account\Models\AccountAccount;
use Yajra\DataTables\DataTables;

class AccountAccountService
{
    public function getDataTable(Request $request)
    {
        $query = AccountAccount::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('type', fn (AccountAccount $account) => Str::headline($account->type))
            ->editColumn('opening_balance', fn (AccountAccount $account) => number_format((float) $account->opening_balance, 2))
            ->editColumn('current_balance', fn (AccountAccount $account) => number_format((float) $account->current_balance, 2))
            ->editColumn('is_active', function (AccountAccount $account) {
                return $account->is_active
                    ? '<span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>'
                    : '<span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Inactive</span>';
            })
            ->editColumn('created_at', fn (AccountAccount $account) => $account->created_at->format('d M Y H:i'))
            ->addColumn('action', function (AccountAccount $account) {
                return view('components.action-buttons', [
                    'id' => $account->id,
                    'edit' => 'accountaccountEdit',
                    'delete' => 'accountaccountDelete',
                ])->render();
            })
            ->rawColumns(['is_active', 'action'])
            ->make(true);
    }

    public function save(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $id = $data['account_id'] ?? null;
                unset($data['account_id']);

                $data['currency_code'] = strtoupper($data['currency_code'] ?? 'BDT');
                $data['is_default'] = (bool) ($data['is_default'] ?? false);
                $data['is_active'] = (bool) ($data['is_active'] ?? true);

                if (empty($data['code'])) {
                    $data['code'] = strtoupper(Str::slug($data['name'], '-'));
                }

                if ($data['is_default']) {
                    AccountAccount::where('type', $data['type'])->update(['is_default' => false]);
                }

                if ($id) {
                    $account = AccountAccount::findOrFail($id);
                    $account->update($data);
                    $message = 'Account updated successfully.';
                } else {
                    $data['current_balance'] = $data['opening_balance'] ?? 0;
                    $data['created_by'] = auth()->id();
                    $account = AccountAccount::create($data);
                    $message = 'Account created successfully.';
                }

                return ['status' => 'success', 'message' => $message, 'account' => $account->fresh()];
            });
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Error saving account: ' . $e->getMessage()];
        }
    }

    public function find(int $id): array
    {
        try {
            return ['status' => 'success', 'account' => AccountAccount::findOrFail($id)];
        } catch (\Exception) {
            return ['status' => 'error', 'message' => 'Account not found.'];
        }
    }

    public function delete(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $account = AccountAccount::findOrFail($id);
                if ($account->transactionLines()->exists() || $account->expenses()->exists()) {
                    return ['status' => 'error', 'message' => 'This account has transactions and cannot be deleted. Deactivate it instead.'];
                }
                $account->delete();
                return ['status' => 'success', 'message' => 'Account deleted successfully.'];
            });
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Error deleting account: ' . $e->getMessage()];
        }
    }

    public function activeOptions()
    {
        return AccountAccount::where('is_active', true)->orderBy('name')->get();
    }
}
