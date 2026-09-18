<?php

namespace Modules\Frontend\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Store\Models\Store;

class AnnouncementBar extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'announcement_bars';

    protected $fillable = [
        'store_id',
        'left_text',
        'center_text',
        'right_text',
        'background_color',
        'text_color',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * NULL store_id = global/platform announcement bar.
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
