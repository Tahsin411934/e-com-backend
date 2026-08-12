<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountInvestment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'account_investments';

    protected $fillable = [
        'investment_no',
        'title',
        'account_id',
        'amount',
        'currency_code',
        'investment_date',
        'investment_type',
        'expected_return',
        'actual_return',
        'status',
        'partner_name',
        'reference_no',
        'attachment_path',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'expected_return' => 'decimal:4',
        'actual_return' => 'decimal:4',
        'investment_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(AccountAccount::class, 'account_id');
    }
}