<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('brand_categories')) {
            Schema::create('brand_categories', function (Blueprint $table) {
                $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
                $table->primary(['brand_id', 'category_id']);
                $table->index('category_id');
            });
        }

        // Preserve existing product-to-category and legacy product.category_id
        // relationships as the initial brand/category assignments.
        $productCategoryPairs = DB::table('products')
            ->join('product_categories', 'product_categories.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->whereNull('product_categories.deleted_at')
            ->whereNotNull('products.brand_id')
            ->select('products.brand_id', 'product_categories.category_id');

        $legacyCategoryPairs = DB::table('products')
            ->whereNull('deleted_at')
            ->whereNotNull('brand_id')
            ->whereNotNull('category_id')
            ->select('brand_id', 'category_id')
            ->union($productCategoryPairs);

        DB::table('brand_categories')->insertUsing(['brand_id', 'category_id'], $legacyCategoryPairs);
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_categories');
    }
};
