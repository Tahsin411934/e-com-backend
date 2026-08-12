<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransfer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'account_transfers';

    protected $fillable = [
        'transfer_no',
        'from_account_id',
        'to_account_id',
        'transaction_id',
        'amount',
        'transfer_fee',
        'currency_code',
        'transferred_at',
        'reference_no',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'transfer_fee' => 'decimal:4',
        'transferred_at' => 'datetime',
    ];

    public function fromAccount()
    {
        return $this->belongsTo(AccountAccount::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(AccountAccount::class, 'to_account_id');
    }
}
