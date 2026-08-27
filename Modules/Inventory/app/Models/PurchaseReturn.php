<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;

class PurchaseReturn extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'purchase_returns';

    protected $fillable = [
        'return_number',
        'purchase_order_id',
        'supplier_id',
        'store_id',
        'created_by',
        'status',
        'refund_status',
        'total_refund_amount',
        'reason',
        'return_date',
        'notes',
    ];

    protected $casts = [
        'total_refund_amount' => 'decimal:2',
        'return_date' => 'date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

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

    public static function generateReturnNumber(): string
    {
        $year = date('Y');

        // Find the highest existing numeric suffix for this year.
        // withTrashed() is important: soft-deleted rows still occupy their
        // unique return_number, so they must be included in the scan.
        $maxSequence = static::withTrashed()
            ->whereYear('created_at', $year)
            ->get(['return_number'])
            ->map(fn ($return) => (int) substr((string) $return->return_number, -4))
            ->max() ?? 0;

        // Increment until we find a number that is actually free
        // (guards against leftover rows with irregular numbers).
        do {
            $maxSequence++;
            $number = 'PR-' . $year . '-' . str_pad($maxSequence, 4, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('return_number', $number)->exists());

        return $number;
    }
}