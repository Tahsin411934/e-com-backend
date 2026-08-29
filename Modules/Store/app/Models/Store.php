<?php

namespace Modules\Store\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

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
