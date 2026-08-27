<?php

namespace Modules\Pos\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Store\Models\Store;

class PosRegister extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'pos_registers';

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'type',
        'status',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function shifts()
    {
        return $this->hasMany(PosShift::class, 'register_id');
    }

    public function sales()
    {
        return $this->hasMany(PosSale::class, 'register_id');
    }
}