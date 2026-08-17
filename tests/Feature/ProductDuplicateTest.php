<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Services\ProductService;
use Tests\TestCase;

class ProductDuplicateTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_product_preserves_variant_option_stock_and_keeps_one_main_image(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('products/original.png', 'original');
        Storage::disk('public')->put('products/secondary.png', 'secondary');

        $brand = Brand::create([
            'name' => 'Dup Brand',
            'slug' => 'dup-brand',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Dup Category',
            'slug' => 'dup-category',
            'status' => 'active',
        ]);

        $source = Product::create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => 'Original Product',
            'slug' => 'original-product-dup-test',
            'product_type' => 'physical',
            'status' => 'active',
            'visibility' => 'public',
        ]);

        $source->categories()->sync([$category->id]);

        $variant = $source->variants()->create([
            'sku' => 'DUP-SKU-001',
            'barcode' => 'DUP-BAR-001',
            'name' => 'Base Variant',
            'sale_price' => 120.00,
            'status' => 'active',
        ]);

        $variant->options()->create([
            'color_name' => 'Red',
            'color_code' => '#ff0000',
            'image_url' => 'products/original.png',
            'price_adjustment' => 10.00,
            'stock' => 42,
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $source->images()->create([
            'image_url' => 'products/original.png',
            'sort_order' => 1,
            'is_main' => true,
        ]);

        $source->images()->create([
            'image_url' => 'products/secondary.png',
            'sort_order' => 2,
            'is_main' => false,
        ]);

        $result = app(ProductService::class)->duplicateProduct($source->id, [
            'name' => 'Copy of Original Product',
            'price' => 150,
        ]);

        $this->assertSame('success', $result['status']);
        $this->assertNotNull($result['product']);
        $this->assertNotSame($source->id, $result['product']->id);
        $this->assertSame(1, $result['product']->images()->where('is_main', true)->count());

        $duplicatedVariant = $result['product']->variants()->first();
        $this->assertNotNull($duplicatedVariant);
        $this->assertNotSame($variant->sku, $duplicatedVariant->sku);
        $this->assertSame(150.0, (float) $duplicatedVariant->sale_price);
        $this->assertSame(42, (int) $duplicatedVariant->options()->first()->stock);
    }
}
