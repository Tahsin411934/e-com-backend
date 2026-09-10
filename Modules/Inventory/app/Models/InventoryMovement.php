<?php

namespace Modules\Inventory\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;

class InventoryMovement extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

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

    /**
     * Movements do not carry their own store_id — they are scoped through
     * the owning inventory location (tenant isolation).
     */
    public function scopeForCurrentStore(Builder $query): Builder
    {
        return $query->whereHas('location', fn ($q) => $q->forCurrentStore());
    }
}
