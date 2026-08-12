<?php

namespace Modules\Account\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Account\Models\AccountAccount;
use Modules\Account\Models\AccountExpense;
use Modules\Account\Models\AccountProductProfitSnapshot;
use Modules\Account\Models\AccountTransaction;

class AccountDashboardService
{
    public function dashboard(Request $request): array
    {
        [$from, $to] = $this->dateRange($request);

        $sales = AccountProductProfitSnapshot::query()
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw('COALESCE(SUM(net_sales), 0) as net_sales')
            ->selectRaw('COALESCE(SUM(cost_total), 0) as cost_total')
            ->selectRaw('COALESCE(SUM(gross_profit), 0) as gross_profit')
            ->first();

        $expenses = AccountExpense::query()
            ->whereHas('category', function ($query) {
                $query->where('type', 'expense');
            })
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $investments = AccountExpense::query()
            ->whereHas('category', function ($query) {
                $query->where('system_key', 'investment');
            })
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $refunds = AccountTransaction::where('type', 'refund')
            ->where('status', 'posted')
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('total_debit');

        $balances = AccountAccount::where('is_active', true)
            ->select('type', DB::raw('COALESCE(SUM(current_balance), 0) as balance'))
            ->groupBy('type')
            ->pluck('balance', 'type');

        $totalCashBalance = AccountAccount::where('is_active', true)
            ->sum('current_balance');

        $accountBalances = AccountAccount::where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $topProducts = AccountProductProfitSnapshot::query()
            ->whereBetween('sold_at', [$from, $to])
            ->select('product_id', 'variant_id', 'product_name', 'variant_name', 'sku')
            ->selectRaw('COALESCE(SUM(quantity), 0) as quantity_sold')
            ->selectRaw('COALESCE(SUM(net_sales), 0) as net_sales')
            ->selectRaw('COALESCE(SUM(cost_total), 0) as cost_total')
            ->selectRaw('COALESCE(SUM(gross_profit), 0) as gross_profit')
            ->groupBy('product_id', 'variant_id', 'product_name', 'variant_name', 'sku')
            ->orderByDesc('gross_profit')
            ->limit(10)
            ->get();

        $lossProducts = AccountProductProfitSnapshot::query()
            ->whereBetween('sold_at', [$from, $to])
            ->select('product_id', 'variant_id', 'product_name', 'variant_name', 'sku')
            ->selectRaw('COALESCE(SUM(quantity), 0) as quantity_sold')
            ->selectRaw('COALESCE(SUM(net_sales), 0) as net_sales')
            ->selectRaw('COALESCE(SUM(cost_total), 0) as cost_total')
            ->selectRaw('COALESCE(SUM(gross_profit), 0) as gross_profit')
            ->groupBy('product_id', 'variant_id', 'product_name', 'variant_name', 'sku')
            ->havingRaw('COALESCE(SUM(gross_profit), 0) < 0')
            ->orderBy('gross_profit')
            ->limit(10)
            ->get();

        $netProfit = (float) ($sales->gross_profit ?? 0) - (float) $expenses - (float) $refunds;
        $netProfitAfterInvestment = $netProfit - (float) $investments;
        $cashMovement = (float) ($sales->net_sales ?? 0)
            - (float) ($sales->cost_total ?? 0)
            - (float) $expenses
            - (float) $refunds
            - (float) $investments;

        return [
            'from' => $from,
            'to' => $to,
            'summary' => [
                'total_income' => (float) ($sales->net_sales ?? 0),
                'total_cost' => (float) ($sales->cost_total ?? 0),
                'gross_profit' => (float) ($sales->gross_profit ?? 0),
                'investments' => (float) $investments,
                'expenses' => (float) $expenses,
                'refunds' => (float) $refunds,
                'net_profit' => $netProfit,
                'net_profit_after_investments' => $netProfitAfterInvestment,
                'cash_movement' => $cashMovement,
                'total_cash_balance' => (float) $totalCashBalance,
                'cash' => (float) ($balances['cash'] ?? 0),
                'bank' => (float) ($balances['bank'] ?? 0),
                'mobile_banking' => (float) ($balances['mobile_banking'] ?? 0),
                'gateway' => (float) ($balances['gateway'] ?? 0),
            ],
            'accountBalances' => $accountBalances,
            'topProducts' => $topProducts,
            'lossProducts' => $lossProducts,
        ];
    }

    private function dateRange(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }
}
