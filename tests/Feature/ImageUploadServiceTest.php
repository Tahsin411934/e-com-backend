<?php

namespace Tests\Feature;

use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadServiceTest extends TestCase
{
    public function test_optimized_product_uploads_have_unique_public_paths(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD with WebP is required.');
        }
        Storage::fake('public');
        $file = UploadedFile::fake()->image('product.png', 2800, 1400);
        $service = new ImageUploadService;
        $first = $service->store($file, 'products', 'product', 'Example Product');
        $second = $service->store($file, 'products', 'product', 'Example Product');

        $this->assertStringEndsWith('.webp', $first);
        $this->assertStringStartsWith('products/example-product-', $first);
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertExists([$first, $second]);
        $this->assertSame('public', Storage::disk('public')->getVisibility($first));
        $size = getimagesizefromstring(Storage::disk('public')->get($first));
        $this->assertSame(2400, $size[0]);
        $this->assertSame(1200, $size[1]);
    }

    public function test_svg_uploads_keep_their_original_contents(): void
    {
        Storage::fake('public');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"/>';
        $file = UploadedFile::fake()->createWithContent('logo.svg', $svg);
        $path = (new ImageUploadService)->store($file, 'brands', 'logo');

        $this->assertStringEndsWith('.svg', $path);
        $this->assertSame($svg, Storage::disk('public')->get($path));
        $this->assertSame('public', Storage::disk('public')->getVisibility($path));
    }
}
