<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class TaxRate extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'tax_rates';

    protected $fillable = [
        'name', 'rate', 'type', 'applies_to', 'status', 'is_default', 'description'
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_default' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_tax_rates');
    }
}