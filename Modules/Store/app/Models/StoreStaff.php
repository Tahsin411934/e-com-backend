<?php

namespace Modules\Store\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Models\User;

class StoreStaff extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'store_staff';

    protected $fillable = [
        'store_id',
        'user_id',
        'staff_code',
        'status',
        'hired_at',
    ];

    protected $casts = [
        'hired_at' => 'date',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
