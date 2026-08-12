<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'account_accounts';

    protected $fillable = [
        'name',
        'code',
        'type',
        'currency_code',
        'opening_balance',
        'current_balance',
        'bank_name',
        'branch_name',
        'account_number',
        'account_holder_name',
        'is_default',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:4',
        'current_balance' => 'decimal:4',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function transactionLines()
    {
        return $this->hasMany(AccountTransactionLine::class, 'account_id');
    }

    public function expenses()
    {
        return $this->hasMany(AccountExpense::class, 'account_id');
    }
}
