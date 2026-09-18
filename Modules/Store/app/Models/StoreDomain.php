<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreDomain extends Model
{
    use HasFactory;

    protected $table = 'store_domains';

    protected $fillable = [
        'store_id',
        'domain',
        'type',
        'is_primary',
        'ssl_status',
        'verified_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function isCustom(): bool
    {
        return $this->type === 'custom';
    }
}
