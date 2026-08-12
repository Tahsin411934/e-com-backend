<?php

namespace Modules\Account\Services;

use Illuminate\Http\Request;
use Modules\Account\Models\AccountProductProfitSnapshot;
use Modules\Account\Models\AccountTransaction;
use Yajra\DataTables\DataTables;

class AccountReportService
{
    public function transactionsDataTable(Request $request)
    {
        $query = AccountTransaction::query()
            ->with(['lines.account', 'lines.category'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        return DataTables::of($query)
            ->editColumn('type', fn (AccountTransaction $transaction) => ucfirst(str_replace('_', ' ', $transaction->type)))
            ->editColumn('total_debit', fn (AccountTransaction $transaction) => number_format((float) $transaction->total_debit, 2))
            ->editColumn('total_credit', fn (AccountTransaction $transaction) => number_format((float) $transaction->total_credit, 2))
            ->editColumn('transaction_date', fn (AccountTransaction $transaction) => $transaction->transaction_date?->format('d M Y H:i') ?? '-')
            ->addColumn('account', function (AccountTransaction $transaction) {
                return $transaction->lines->pluck('account.name')->filter()->unique()->implode(', ') ?: '-';
            })
            ->addColumn('category', function (AccountTransaction $transaction) {
                return $transaction->lines->pluck('category.name')->filter()->unique()->implode(', ') ?: '-';
            })
            ->make(true);
    }

    public function productProfitDataTable(Request $request)
    {
        $query = AccountProductProfitSnapshot::query()
            ->select('product_id', 'variant_id', 'product_name', 'variant_name', 'sku')
            ->selectRaw('COALESCE(SUM(quantity), 0) as quantity_sold')
            ->selectRaw('COALESCE(SUM(net_sales), 0) as net_sales')
            ->selectRaw('COALESCE(SUM(cost_total), 0) as cost_total')
            ->selectRaw('COALESCE(SUM(gross_profit), 0) as gross_profit')
            ->selectRaw('CASE WHEN SUM(net_sales) > 0 THEN (SUM(gross_profit) / SUM(net_sales)) * 100 ELSE 0 END as profit_margin')
            ->groupBy('product_id', 'variant_id', 'product_name', 'variant_name', 'sku');

        if ($request->filled('from')) {
            $query->whereDate('sold_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('sold_at', '<=', $request->input('to'));
        }

        return DataTables::of($query)
            ->editColumn('quantity_sold', fn ($row) => number_format((float) $row->quantity_sold, 2))
            ->editColumn('net_sales', fn ($row) => number_format((float) $row->net_sales, 2))
            ->editColumn('cost_total', fn ($row) => number_format((float) $row->cost_total, 2))
            ->editColumn('gross_profit', function ($row) {
                $class = (float) $row->gross_profit < 0 ? 'text-red-600' : 'text-green-600';
                return '<span class="' . $class . ' font-semibold">' . number_format((float) $row->gross_profit, 2) . '</span>';
            })
            ->editColumn('profit_margin', fn ($row) => number_format((float) $row->profit_margin, 2) . '%')
            ->rawColumns(['gross_profit'])
            ->make(true);
    }
}
