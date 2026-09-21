<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'type', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function plans() { return $this->belongsToMany(Plan::class, 'plan_features')->withPivot(['enabled', 'limit_value', 'configuration'])->withTimestamps(); }
}
