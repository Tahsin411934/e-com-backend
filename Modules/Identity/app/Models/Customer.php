<?php

namespace Modules\Identity\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Modules\Store\Models\Store;

class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'store_id', 'first_name', 'last_name', 'email', 'phone', 'password_hash',
        'status', 'email_verified_at', 'first_login_at', 'last_login_at',
    ];

    protected $hidden = ['password_hash', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'first_login_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
