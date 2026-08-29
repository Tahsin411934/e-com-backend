<?php

namespace Modules\Inventory\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;

class PurchaseOrder extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'store_id',
        'status',
        'total_amount',
        'shipping_cost',
        'tax_amount',
        'discount_amount',
        'payment_status',
        'expected_delivery_date',
        'received_date',
        'notes',
        'order_date',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'received_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class, 'purchase_order_id');
    }

    /**
     * Total amount paid against this order (from supplier_payments).
     */
    public function getPaidAmountAttribute(): float
    {
        $paid = $this->payments()
            ->whereNull('deleted_at')
            ->sum('amount');

        return (float) $paid;
    }

    public static function generatePoNumber(): string
    {
        $year = date('Y');

        // withTrashed() is important: soft-deleted orders keep their unique
        // po_number, so they must be counted to avoid duplicate numbers.
        $maxSequence = static::withTrashed()
            ->whereYear('created_at', $year)
            ->get(['po_number'])
            ->map(fn ($po) => (int) substr((string) $po->po_number, -4))
            ->max() ?? 0;

        do {
            $maxSequence++;
            $number = 'PO-'.$year.'-'.str_pad($maxSequence, 4, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('po_number', $number)->exists());

        return $number;
    }
}
