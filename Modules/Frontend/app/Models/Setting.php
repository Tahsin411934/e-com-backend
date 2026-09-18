<?php

namespace Modules\Frontend\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Store\Models\Store;

class Setting extends Model
{
    use CustomSoftDeletes;

    protected $table = 'settings';

    protected $fillable = [
        'store_id',
        'group',
        'key',
        'value',
        'type',
        'label',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * NULL store_id = global/platform setting; store rows override global values per key.
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
