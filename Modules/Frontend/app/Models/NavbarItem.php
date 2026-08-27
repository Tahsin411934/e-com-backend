<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDeletes;

class NavbarItem extends Model
{
    use HasFactory;
    use CustomSoftDeletes;

    protected $table = 'navbar_items';

    protected $fillable = ['name', 'slug', 'url', 'icon', 'sort_order', 'status'];

    public function subnavbarItems()
    {
        return $this->hasMany(SubnavbarItem::class, 'navbar_item_id');
    }
}