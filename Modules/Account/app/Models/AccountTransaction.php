<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'account_transactions';

    protected $fillable = [
        'transaction_no',
        'type',
        'status',
        'source_type',
        'source_id',
        'currency_code',
        'total_debit',
        'total_credit',
        'transaction_date',
        'reference_no',
        'description',
        'metadata',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'total_debit' => 'decimal:4',
        'total_credit' => 'decimal:4',
        'transaction_date' => 'datetime',
        'metadata' => 'array',
        'posted_at' => 'datetime',
    ];

    public function source()
    {
        return $this->morphTo();
    }

    public function lines()
    {
        return $this->hasMany(AccountTransactionLine::class, 'transaction_id');
    }
}
