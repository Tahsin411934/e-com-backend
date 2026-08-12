<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountExpense extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'account_expenses';

    protected $fillable = [
        'expense_no',
        'account_id',
        'category_id',
        'transaction_id',
        'amount',
        'currency_code',
        'expense_date',
        'vendor_name',
        'reference_no',
        'attachment_path',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'expense_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(AccountAccount::class, 'account_id');
    }

    public function category()
    {
        return $this->belongsTo(AccountCategory::class, 'category_id');
    }

    public function transaction()
    {
        return $this->belongsTo(AccountTransaction::class, 'transaction_id');
    }
}
