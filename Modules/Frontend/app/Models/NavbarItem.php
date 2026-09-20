<?php

namespace Modules\Frontend\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Store\Models\Store;

class NavbarItem extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'navbar_items';

    protected $fillable = ['store_id', 'name', 'slug', 'url', 'icon', 'sort_order', 'status', 'is_central_navbar_item'];

    protected $casts = [
        'is_central_navbar_item' => 'boolean',
    ];

    /**
     * NULL store_id = global/platform navbar item.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    public function subnavbarItems()
    {
        return $this->hasMany(SubnavbarItem::class, 'navbar_item_id');
    }
}
