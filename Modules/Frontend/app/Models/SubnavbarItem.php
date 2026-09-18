<?php

namespace Modules\Frontend\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Store\Models\Store;

class SubnavbarItem extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'subnavbar_items';

    protected $fillable = ['store_id', 'navbar_item_id', 'name', 'slug', 'url', 'icon', 'sort_order', 'status'];

    public function navbarItem()
    {
        return $this->belongsTo(NavbarItem::class, 'navbar_item_id');
    }

    /**
     * NULL store_id = global/platform subnavbar item.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }
}
