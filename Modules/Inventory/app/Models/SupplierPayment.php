<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;
use Modules\Account\Models\AccountAccount;
use Modules\Account\Models\AccountTransaction;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;

class SupplierPayment extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'supplier_payments';

    protected $fillable = [
        'payment_no',
        'supplier_id',
        'purchase_order_id',
        'store_id',
        'account_id',
        'transaction_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_no',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function account()
    {
        return $this->belongsTo(AccountAccount::class, 'account_id');
    }

    public function transaction()
    {
        return $this->belongsTo(AccountTransaction::class, 'transaction_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generatePaymentNo(): string
    {
        $year = date('Y');
        $maxSequence = static::withTrashed()
            ->whereYear('created_at', $year)
            ->get(['payment_no'])
            ->map(fn ($payment) => (int) substr((string) $payment->payment_no, -4))
            ->max() ?? 0;

        do {
            $maxSequence++;
            $number = 'PPM-' . $year . '-' . str_pad($maxSequence, 4, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('payment_no', $number)->exists());

        return $number;
    }
}