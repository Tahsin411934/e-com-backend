<?php

namespace Modules\Frontend\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'banner_image' => $this->banner_image ? asset('storage/'.$this->banner_image) : null,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'smtag' => $this->smtag,
            'primary_btn' => $this->primary_btn,
            'primary_btn_url' => $this->primary_btn_url,
            'primary_btn_color' => $this->primary_btn_color,
            'primary_btn_text_color' => $this->primary_btn_text_color,
            'secondary_btn' => $this->secondary_btn,
            'secondary_btn_url' => $this->secondary_btn_url,
            'secondary_btn_color' => $this->secondary_btn_color,
            'secondary_btn_text_color' => $this->secondary_btn_text_color,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
