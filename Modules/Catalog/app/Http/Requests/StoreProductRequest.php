<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Catalog\Models\Product;
use Modules\Store\Support\CurrentStore;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => 'nullable|integer|exists:stores,id',
            'brand_id' => 'nullable|integer|exists:brands,id',
            'unit_id' => 'nullable|integer|exists:units,id',
            'size_id' => 'nullable|integer|exists:sizes,id',
            'tax_rate_id' => 'nullable|integer|exists:tax_rates,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
            'navbar_item_id' => 'nullable|integer|exists:navbar_items,id',
            'subnavbar_item_id' => 'nullable|integer|exists:subnavbar_items,id',
            'name' => 'required|string|max:220',
            'slug' => ['required', 'string', 'max:240', Rule::unique('products', 'slug')->where('store_id', $this->slugStoreId())],
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'product_type' => 'required|in:physical,digital,service,bundle',
            'delivery_charge' => 'nullable|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'status' => 'required|in:draft,active,archived',
            'visibility' => 'required|in:public,hidden,private',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
            'is_homepage' => 'nullable|boolean',
            'main_image_id' => 'nullable|string|max:50',

            // Variant validation
            'variants' => 'nullable|array',
            'variants.*.sku' => 'required_with:variants|string|max:100',
            'variants.*.name' => 'required_with:variants|string|max:220',
            'variants.*.sale_price' => 'required_with:variants|numeric|min:0',
            'variants.*.cost_price' => 'nullable|numeric|min:0',
            'variants.*.compare_at_price' => 'nullable|numeric|min:0',
            'variants.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'variants.*.barcode' => 'nullable|string|max:100',
            'variants.*.weight_grams' => 'nullable|integer|min:0',
            'variants.*.track_inventory' => 'nullable|boolean',
            'variants.*.allow_backorder' => 'nullable|boolean',
            'variants.*.attributes' => 'nullable|array',
            'variants.*.options' => 'nullable|array',
            'variants.*.options.*.id' => 'nullable|integer',
            'variants.*.options.*.color_name' => 'nullable|string|max:100',
            'variants.*.options.*.color_code' => 'nullable|string|max:20',
            'variants.*.options.*.sku' => 'required_with:variants.*.options|string|max:100',
            'variants.*.options.*.barcode' => 'nullable|string|max:100',
            'variants.*.options.*.price_adjustment' => 'nullable|numeric',
            'variants.*.options.*.cost_price' => 'nullable|numeric|min:0',
            'variants.*.options.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.options.*.compare_at_price' => 'nullable|numeric|min:0',
            'variants.*.options.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'variants.*.options.*.sort_order' => 'nullable|integer|min:0',
        ];
    }

    protected function slugStoreId(?Product $product = null): ?int
    {
        $actor = $this->user();
        $canAssignStore = $actor && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin'));

        if ($canAssignStore && $this->exists('store_id')) {
            $storeId = $this->input('store_id');

            // Creation falls back to CurrentStore in BelongsToStore.
            return $storeId ? (int) $storeId : ($product ? null : CurrentStore::id());
        }

        return $product ? $product->store_id : CurrentStore::id();
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'slug.required' => 'Product slug is required.',
            'slug.unique' => 'Product slug must be unique within this store.',
            'product_type.in' => 'Product type is invalid.',
            'status.in' => 'Product status is invalid.',
            'visibility.in' => 'Product visibility is invalid.',
        ];
    }
}
