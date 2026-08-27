<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;
use Modules\Identity\Models\User;

class InventoryMovement extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'inventory_movements';

    protected $fillable = [
        'location_id',
        'variant_id',
        'movement_type',
        'quantity',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
    ];

    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}