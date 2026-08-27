<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class Country extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

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