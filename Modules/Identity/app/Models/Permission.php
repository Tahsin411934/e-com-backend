<?php

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;
use Modules\Identity\Models\Role;

class Permission extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'description',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }
}