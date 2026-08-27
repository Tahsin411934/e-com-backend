<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class AccountCategory extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'account_categories';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'type',
        'system_key',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
