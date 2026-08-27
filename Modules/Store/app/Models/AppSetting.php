<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class AppSetting extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'app_settings';

    protected $fillable = [
        'scope_type',
        'scope_id',
        'setting_key',
        'setting_value',
        'is_public',
    ];

    protected $casts = [
        'setting_value' => 'json',
        'is_public' => 'boolean',
    ];
}