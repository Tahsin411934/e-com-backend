<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Services\ProductService;
use Modules\Store\Support\CurrentStore;
use Tests\TestCase;

class ProductBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $slug): Product
    {
        return Product::create([
            'name' => $slug, 'slug' => $slug, 'product_type' => 'physical',
            'status' => 'active', 'visibility' => 'public',
        ]);
    }

    public function test_bulk_delete_removes_selected_products_and_children_only(): void
    {
        CurrentStore::set(null);
        $first = $this->product('first');
        $second = $this->product('second');
        $keep = $this->product('keep');
        $variant = $first->variants()->create([
            'sku' => 'BULK-001', 'name' => 'Default', 'sale_price' => 100, 'status' => 'active',
        ]);
        $option = $variant->options()->create(['color_name' => 'Red', 'status' => 'active']);
        $image = $first->images()->create(['image_url' => 'products/test.png']);

        $response = app(ProductService::class)->deleteProducts([$first->id, $second->id]);

        $this->assertSame(200, $response->getStatusCode());
        foreach ([$first, $second, $variant, $option, $image] as $model) {
            $this->assertSoftDeleted($model);
        }
        $this->assertNotNull(Product::find($keep->id));
    }

    public function test_missing_or_out_of_scope_product_prevents_partial_deletion(): void
    {
        CurrentStore::set(null);
        $first = $this->product('first');
        $other = $this->product('other');

        $response = app(ProductService::class)->deleteProducts([$first->id, $other->id + 1000]);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertNotNull(Product::find($first->id));

        CurrentStore::set(999999);
        $response = app(ProductService::class)->deleteProducts([$first->id, $other->id]);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertNotNull(Product::find($first->id));
        $this->assertNotNull(Product::find($other->id));
    }
}
