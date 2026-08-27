<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;
use Modules\Store\Models\StoreStaff;
use Modules\Store\Models\Address;

class Store extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'stores';

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'status',
        'currency_code',
        'timezone',
    ];

    public function staff()
    {
        return $this->hasMany(StoreStaff::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}