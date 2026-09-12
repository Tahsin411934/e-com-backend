<?php

namespace Modules\Reviews\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Models\User;

class Notification extends Model
{
    use CustomSoftDeletes, HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id', 'type', 'channel', 'subject', 'body', 'data', 'read_at', 'sent_at',
    ];

    protected $casts = [
        'data' => 'json',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($q)
    {
        return $q->whereNull('read_at');
    }
}
