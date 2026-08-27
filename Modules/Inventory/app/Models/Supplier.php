<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class Supplier extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'contact_person',
        'address',
        'city',
        'country',
        'tax_number',
        'payment_terms',
        'notes',
        'status',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class, 'supplier_id');
    }

    public function products()
    {
        return $this->belongsToMany(
            \Modules\Catalog\Models\Product::class,
            'product_supplier',
            'supplier_id',
            'product_id'
        )->withPivot('supplier_sku', 'lead_time_days', 'minimum_order_qty', 'default_unit_cost');
    }
}