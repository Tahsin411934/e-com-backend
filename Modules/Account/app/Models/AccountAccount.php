<?php

namespace Modules\Account\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountAccount extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

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
