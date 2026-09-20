<?php

namespace Modules\Frontend\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Store\Models\Store;

class Banner extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'banners';

    protected $fillable = [
        'store_id',
        'banner_image',
        'title',
        'subtitle',
        'smtag',
        'primary_btn',
        'primary_btn_url',
        'primary_btn_color',
        'primary_btn_text_color',
        'secondary_btn',
        'secondary_btn_url',
        'secondary_btn_color',
        'secondary_btn_text_color',
        'sort_order',
        'status',
        'is_central_banner',
    ];

    protected $casts = [
        'is_central_banner' => 'boolean',
    ];

    /**
     * NULL store_id = global/platform banner.
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
