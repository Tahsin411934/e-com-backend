<?php

namespace Modules\Store\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'countries';

    protected $fillable = [
        'iso2',
        'name',
    ];

    public $timestamps = false;

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
