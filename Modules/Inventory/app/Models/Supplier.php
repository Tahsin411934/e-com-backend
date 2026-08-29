<?php

namespace Modules\Inventory\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Catalog\Models\Product;

class Supplier extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

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
            Product::class,
            'product_supplier',
            'supplier_id',
            'product_id'
        )->withPivot('supplier_sku', 'lead_time_days', 'minimum_order_qty', 'default_unit_cost');
    }
}
