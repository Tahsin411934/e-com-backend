<?php

namespace Modules\Store\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Models\User;

class Address extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'addresses';

    protected $fillable = [
        'user_id',
        'store_id',
        'label',
        'contact_name',
        'contact_phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country_id',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
