<?php

require 'vendor/autoload.php';

// Bootstrap the application
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Test the feature directly
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Services\ProductService;
use Illuminate\Support\Facades\DB;

// Clear and verify table
DB::table('products')->truncate();

// Create test data
$brand = Brand::create([
    'name' => 'Sort Test Brand',
    'slug' => 'sort-test-brand',
    'status' => 'active',
]);

$p1 = Product::create([
    'brand_id' => $brand->id,
    'name' => 'Product 1',
    'slug' => 'product-1-sort-test',
    'product_type' => 'physical',
    'status' => 'active',
    'visibility' => 'public',
    'order_column' => 1,
]);

$p2 = Product::create([
    'brand_id' => $brand->id,
    'name' => 'Product 2',
    'slug' => 'product-2-sort-test',
    'product_type' => 'physical',
    'status' => 'active',
    'visibility' => 'public',
    'order_column' => 2,
]);

$p3 = Product::create([
    'brand_id' => $brand->id,
    'name' => 'Product 3',
    'slug' => 'product-3-sort-test',
    'product_type' => 'physical',
    'status' => 'active',
    'visibility' => 'public',
    'order_column' => 3,
]);

// Test ordered() scope before reorder
$sorted_before = Product::ordered()->pluck('id')->all();
echo "Before reorder: " . json_encode($sorted_before) . "\n";
assert($sorted_before === [$p1->id, $p2->id, $p3->id], "Initial order should be [1,2,3]");

// Reorder using service
$result = app(ProductService::class)->reorderProducts([$p3->id, $p1->id, $p2->id]);
echo "Reorder result: " . json_encode($result) . "\n";
assert($result['status'] === 'success', "Reorder should succeed");

// Test ordered() scope after reorder
$sorted_after = Product::ordered()->pluck('id')->all();
echo "After reorder: " . json_encode($sorted_after) . "\n";
assert($sorted_after === [$p3->id, $p1->id, $p2->id], "Order should be [3,1,2] after reorder");

echo "✓ All assertions passed!\n";
