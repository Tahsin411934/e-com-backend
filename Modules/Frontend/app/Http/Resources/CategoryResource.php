<?php

namespace Modules\Frontend\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $imageUrl = null;
        if ($this->image) {
            $imageUrl = asset('storage/'.$this->image);
        } elseif ($this->image_url) {
            $imageUrl = $this->image_url;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $imageUrl,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'products_count' => $this->products_count ?? 0,
        ];
    }
}
