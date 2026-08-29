<?php

namespace Modules\Identity\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'description',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }
}
