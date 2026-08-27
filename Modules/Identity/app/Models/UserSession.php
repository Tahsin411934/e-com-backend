<?php

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;
use Modules\Identity\Models\User;

class UserSession extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'token_hash',
        'ip_address',
        'user_agent',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}