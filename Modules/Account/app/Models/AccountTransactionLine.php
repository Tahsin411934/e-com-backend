<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTransactionLine extends Model
{
    use HasFactory;

    protected $table = 'account_transaction_lines';

    protected $fillable = [
        'transaction_id',
        'account_id',
        'category_id',
        'entry_type',
        'amount',
        'memo',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
    ];

    public function transaction()
    {
        return $this->belongsTo(AccountTransaction::class, 'transaction_id');
    }

    public function account()
    {
        return $this->belongsTo(AccountAccount::class, 'account_id');
    }

    public function category()
    {
        return $this->belongsTo(AccountCategory::class, 'category_id');
    }
}
