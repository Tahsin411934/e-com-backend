<?php

namespace Modules\Inventory\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Store\Models\Store;

class InventoryLocation extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'inventory_locations';

    protected $fillable = [
        'store_id',
        'name',
        'location_type',
        'status',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function stock()
    {
        return $this->hasMany(InventoryStock::class, 'location_id');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'location_id');
    }
}
