<?php

namespace Modules\Frontend\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NavbarItem extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'navbar_items';

    protected $fillable = ['name', 'slug', 'url', 'icon', 'sort_order', 'status'];

    public function subnavbarItems()
    {
        return $this->hasMany(SubnavbarItem::class, 'navbar_item_id');
    }
}
