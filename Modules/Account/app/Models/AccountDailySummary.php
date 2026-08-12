<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDailySummary extends Model
{
    use HasFactory;

    protected $table = 'account_daily_summaries';

    protected $fillable = [
        'summary_date',
        'account_id',
        'store_id',
        'opening_balance',
        'closing_balance',
        'total_income',
        'total_expense',
        'total_sales',
        'total_refunds',
        'total_cogs',
        'gross_profit',
        'net_profit',
        'metadata',
    ];

    protected $casts = [
        'summary_date' => 'date',
        'opening_balance' => 'decimal:4',
        'closing_balance' => 'decimal:4',
        'total_income' => 'decimal:4',
        'total_expense' => 'decimal:4',
        'total_sales' => 'decimal:4',
        'total_refunds' => 'decimal:4',
        'total_cogs' => 'decimal:4',
        'gross_profit' => 'decimal:4',
        'net_profit' => 'decimal:4',
        'metadata' => 'array',
    ];
}
