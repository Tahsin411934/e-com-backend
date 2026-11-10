<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Catalog\Models\Product;
use Modules\Frontend\Services\CategoryProductSorting;
use Tests\TestCase;

class CategoryProductSortingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('products', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->integer('order_column');
            $table->timestamp('published_at'); $table->unsignedBigInteger('store_id')->nullable(); $table->softDeletes();
        });
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('product_id'); $table->decimal('sale_price', 12, 4);
            $table->decimal('discount_percent', 8, 4)->default(0); $table->string('status')->default('active'); $table->softDeletes();
        });
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('store_id')->nullable(); $table->boolean('is_active')->default(true);
            $table->string('status')->default('active'); $table->integer('priority')->default(0);
            $table->timestamp('starts_at')->nullable(); $table->timestamp('ends_at')->nullable(); $table->softDeletes();
        });
        Schema::create('campaign_products', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('campaign_id'); $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable(); $table->string('discount_type');
            $table->decimal('discount_value', 12, 4); $table->softDeletes();
        });
        DB::table('products')->insert([
            ['id' => 1, 'name' => 'Zulu', 'order_column' => 1, 'published_at' => '2025-01-01'],
            ['id' => 2, 'name' => 'Alpha', 'order_column' => 99, 'published_at' => '2025-03-01'],
            ['id' => 3, 'name' => 'Beta', 'order_column' => 50, 'published_at' => '2025-02-01'],
            ['id' => 4, 'name' => 'Empty', 'order_column' => 0, 'published_at' => '2024-01-01'],
        ]);
        DB::table('product_variants')->insert([
            ['id' => 1, 'product_id' => 1, 'sale_price' => 500, 'discount_percent' => 0],
            ['id' => 2, 'product_id' => 2, 'sale_price' => 100, 'discount_percent' => 20],
            ['id' => 3, 'product_id' => 3, 'sale_price' => 200, 'discount_percent' => 0],
        ]);
    }

    private function ids(string $sort): array
    {
        $query = Product::query()->orderBy('order_column');
        app(CategoryProductSorting::class)->apply($query, $sort);
        return $query->get()->pluck('id')->all();
    }

    public function test_customer_sort_overrides_manual_order_and_keeps_unpriced_products_last(): void
    {
        $this->assertSame([2, 3, 1, 4], $this->ids('price_asc'));
        $this->assertSame([1, 3, 2, 4], $this->ids('price_desc'));
        $this->assertSame([2, 3, 4, 1], $this->ids('name'));
        $this->assertSame([2, 3, 1, 4], $this->ids('latest'));
    }

    public function test_live_campaign_price_is_used_and_expired_campaign_is_ignored(): void
    {
        DB::table('campaigns')->insert(['id' => 1]);
        DB::table('campaign_products')->insert([
            'campaign_id' => 1, 'product_id' => 1, 'discount_type' => 'fixed_price', 'discount_value' => 50,
        ]);
        $this->assertSame([1, 2, 3, 4], $this->ids('price_asc'));
        DB::table('campaigns')->where('id', 1)->update(['ends_at' => '2020-01-01']);
        $this->assertSame([2, 3, 1, 4], $this->ids('price_asc'));
    }

    public function test_variant_offer_precedes_product_offer_and_non_discount_offer_falls_back(): void
    {
        DB::table('campaigns')->insert(['id' => 1]);
        DB::table('campaign_products')->insert([
            ['campaign_id' => 1, 'product_id' => 2, 'variant_id' => null, 'discount_type' => 'fixed_price', 'discount_value' => 10],
            ['campaign_id' => 1, 'product_id' => 2, 'variant_id' => 2, 'discount_type' => 'fixed_price', 'discount_value' => 150],
        ]);
        $query = Product::query();
        app(CategoryProductSorting::class)->apply($query, 'price_asc');
        $this->assertEquals(80, $query->first()->category_sort_price);
        $this->assertSame([2, 3, 1, 4], $this->ids('price_asc'));
    }
}
