<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Store\Models\Store;

class ServiceBenefit extends Model
{
    protected $table = 'store_service_benefits';

    protected $fillable = ['store_id', 'title', 'description', 'icon', 'sort_order', 'is_active'];

    protected $casts = ['sort_order' => 'integer', 'is_active' => 'boolean'];

    // Stable keys shared with the storefront; no arbitrary HTML or icon class names.
    public const ICONS = [
        'truck' => ['label' => 'Delivery', 'class' => 'fa-solid fa-truck'],
        'wallet' => ['label' => 'Cash / Payment', 'class' => 'fa-solid fa-wallet'],
        'refresh-cw' => ['label' => 'Returns', 'class' => 'fa-solid fa-rotate'],
        'shield-check' => ['label' => 'Security', 'class' => 'fa-solid fa-shield-halved'],
        'headphones' => ['label' => 'Support', 'class' => 'fa-solid fa-headphones'],
        'package' => ['label' => 'Package', 'class' => 'fa-solid fa-box'],
        'badge-check' => ['label' => 'Quality', 'class' => 'fa-solid fa-circle-check'],
        'clock' => ['label' => 'Time', 'class' => 'fa-solid fa-clock'],
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
