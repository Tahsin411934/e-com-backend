<?php

namespace Modules\Identity\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Modules\Store\Models\Store;

class User extends Authenticatable
{
    use CustomSoftDeletes, HasApiTokens, HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'public_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password_hash',
        'status',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    public function getNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Store owned by this user (SaaS tenant relationship).
     */
    public function ownedStore()
    {
        return $this->hasOne(Store::class, 'owner_id');
    }

    /**
     * Determine whether the user is a SaaS store owner (tenant).
     */
    public function isStoreOwner(): bool
    {
        return $this->hasRole(self::STORE_OWNER_ROLE);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles->contains(function ($role) use ($roleNames) {
            return in_array($role->name, $roleNames);
        });
    }

    /**
     * Role name reserved for frontend/shop customers
     */
    public const CUSTOMER_ROLE = 'Customer';

    /**
     * Role name reserved for SaaS store owners (tenants)
     */
    public const STORE_OWNER_ROLE = 'Store Owner';

    /**
     * Role name reserved for SaaS store staff members
     */
    public const STORE_STAFF_ROLE = 'Store Staff';

    /**
     * Determine whether the user may access the admin panel.
     *
     * Admin panel access requires at least one staff-side role
     * (Super Admin, Admin, Manager, Staff, ...). Frontend-registered
     * customers only carry the "Customer" role (or no role at all)
     * and are denied access.
     */
    public function hasAdminAccess(): bool
    {
        return $this->roles->contains(function ($role) {
            return $role->name !== self::CUSTOMER_ROLE;
        });
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permissionName): bool
    {
        // Super Admin has all permissions
        if ($this->hasRole('Super Admin')) {
            return true;
        }

        return $this->roles
            ->flatMap->permissions
            ->contains('name', $permissionName);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        // Super Admin has all permissions
        if ($this->hasRole('Super Admin')) {
            return true;
        }

        $userPermissions = $this->roles
            ->flatMap->permissions
            ->pluck('name');

        return collect($permissionNames)->contains(function ($permission) use ($userPermissions) {
            return $userPermissions->contains($permission);
        });
    }

    /**
     * Get all permission names for the user
     */
    public function getAllPermissionNames(): Collection
    {
        return $this->roles
            ->flatMap->permissions
            ->pluck('name')
            ->unique();
    }

    /**
     * Get the user's primary role name
     */
    public function getPrimaryRoleAttribute(): ?string
    {
        return $this->roles->first()?->name;
    }
}
