<?php

namespace Modules\Frontend\Models;

use App\Traits\CustomSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubnavbarItem extends Model
{
    use CustomSoftDeletes;
    use HasFactory;

    protected $table = 'subnavbar_items';

    protected $fillable = ['navbar_item_id', 'name', 'slug', 'url', 'icon', 'sort_order', 'status'];

    public function navbarItem()
    {
        return $this->belongsTo(NavbarItem::class, 'navbar_item_id');
    }
}
