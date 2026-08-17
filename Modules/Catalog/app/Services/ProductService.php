<?php

namespace Modules\Catalog\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\ProductImage;
use Modules\Catalog\Models\ProductVariant;
use Modules\Catalog\Models\VariantOption;
use Yajra\DataTables\DataTables;

class ProductService
{

    public function getProductDataTable(Request $request)
    {
        $query = Product::with(['brand'])
            ->select([
                'products.id',
                'products.brand_id',
                'products.name',
                'products.slug',
                'products.product_type',
                'products.status',
                'products.visibility',
                'products.created_at',
                'products.order_column',
            ])
            ->orderBy('products.order_column');

        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->category_id) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        return DataTables::of($query)
            ->editColumn('status', function (Product $product) {
                return statusBadge($product->status);
            })
            ->editColumn('visibility', function (Product $product) {
                return ucfirst($product->visibility);
            })
            ->editColumn('created_at', function (Product $product) {
                return $product->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Product $product) {
                return view('components.action-buttons', [
                    'id' => $product->id,
                    'edit' => 'productEdit',
                    'delete' => 'productDelete',
                    'duplicate' => 'productDuplicate',
                ])->render();
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function saveProduct(array $data): array
    {
        // Validate SKU uniqueness across all variants (create + update)
        if (isset($data['variants']) && is_array($data['variants'])) {
            $allSkus = [];
            foreach ($data['variants'] as $index => $variantData) {
                $sku = trim($variantData['sku'] ?? '');
                if (empty($sku)) continue;

                // Check for duplicate SKUs within the same request
                if (in_array($sku, $allSkus)) {
                    return [
                        'status' => 'error',
                        'message' => "Duplicate SKU '{$sku}' found at variant #" . ($index + 1) . '. Each variant must have a unique SKU.',
                    ];
                }
                $allSkus[] = $sku;
            }

            // Check for duplicate SKUs against existing DB records
            // Exclude ALL existing variants of THIS product so they can keep their SKUs unchanged
            $productId = $data['product_id'] ?? null;
            $existingVariantIds = [];
            if ($productId) {
                $product = \Modules\Catalog\Models\Product::find($productId);
                if ($product) {
                    $existingVariantIds = $product->variants()->pluck('id')->toArray();
                }
            }

            foreach ($data['variants'] as $index => $variantData) {
                $sku = trim($variantData['sku'] ?? '');
                if (empty($sku)) continue;

                // Exclude ALL existing variants of this product from the check
                // so that unchanged variants don't trigger false conflicts
                $conflict = \Modules\Catalog\Models\ProductVariant::where('sku', $sku)
                    ->whereNotIn('id', $existingVariantIds)
                    ->exists();

                if ($conflict) {
                    return [
                        'status' => 'error',
                        'message' => "SKU '{$sku}' at variant #" . ($index + 1) . ' already exists in the database.',
                    ];
                }
            }
        }

        try {
            return DB::transaction(function () use ($data) {
                $productId = $data['product_id'] ?? null;
                $oldSlug = null;

                if ($productId) {
                    $product = Product::findOrFail($productId);
                    $oldSlug = $product->slug;
                    $product->update($data);
                    $message = 'Product updated successfully.';
                } else {
                    $product = Product::create($data);
                    $message = 'Product created successfully.';
                }

                if (isset($data['category_ids']) && is_array($data['category_ids'])) {
                    $product->categories()->sync($data['category_ids']);
                }

                if (isset($data['deleted_image_ids']) && is_array($data['deleted_image_ids'])) {
                    $this->removeProductImages($product, $data['deleted_image_ids']);
                }

                if (isset($data['images']) && is_array($data['images'])) {
                    $this->saveProductImages($product, $data['images']);
                }

                // Set main/featured image
                if (isset($data['main_image_id'])) {
                    $mainImageId = $data['main_image_id'];

                    if (is_string($mainImageId) && str_starts_with($mainImageId, 'new_')) {
                        $imageIndex = (int) substr($mainImageId, 4);
                        $uploadedImages = $data['images'] ?? [];
                        if (isset($uploadedImages[$imageIndex]) && $uploadedImages[$imageIndex] instanceof \Illuminate\Http\UploadedFile) {
                            $allImages = ProductImage::where('product_id', $product->id)
                                ->orderBy('id')
                                ->get();
                            $existingCount = $allImages->count() - count($uploadedImages);
                            $targetImage = $allImages->skip($existingCount + $imageIndex)->first();
                            if ($targetImage) {
                                ProductImage::where('product_id', $product->id)->update(['is_main' => false]);
                                $targetImage->update(['is_main' => true]);
                            }
                        }
                    } elseif (is_numeric($mainImageId) && $mainImageId > 0) {
                        $mainImageId = (int) $mainImageId;
                        ProductImage::where('product_id', $product->id)->update(['is_main' => false]);
                        ProductImage::where('id', $mainImageId)->where('product_id', $product->id)->update(['is_main' => true]);
                    }
                }

                if (!ProductImage::where('product_id', $product->id)->where('is_main', true)->exists()) {
                    $firstImage = ProductImage::where('product_id', $product->id)->orderBy('sort_order')->first();
                    if ($firstImage) {
                        $firstImage->update(['is_main' => true]);
                    }
                }

                // Handle explicitly deleted variants from edit form
                if (isset($data['deleted_variant_ids']) && is_array($data['deleted_variant_ids'])) {
                    $product->variants()->whereIn('id', $data['deleted_variant_ids'])->delete();
                }

                if (isset($data['variants']) && is_array($data['variants'])) {
                    $keepVariantIds = [];
                    
                    foreach ($data['variants'] as $variantData) {
                        // Handle default values for checkboxes if missing
                        $variantData['track_inventory'] = $variantData['track_inventory'] ?? false;
                        $variantData['allow_backorder'] = $variantData['allow_backorder'] ?? false;
                        
                        // Extract options before saving variant
                        $optionsData = $variantData['options'] ?? [];
                        unset($variantData['options']);
                        
                        $variant = $product->variants()->updateOrCreate(
                            ['id' => $variantData['id'] ?? null], 
                            $variantData
                        );
                        $keepVariantIds[] = $variant->id;

                        // Handle variant_options (color variants for this size)
                        if (!empty($optionsData) && is_array($optionsData)) {
                            $keepOptionIds = [];
                            foreach ($optionsData as $optData) {
                                $optData['product_variant_id'] = $variant->id;
                                $optData['status'] = $optData['status'] ?? 'active';
                                $optData['sort_order'] = $optData['sort_order'] ?? 0;
                                $optData['stock'] = $optData['stock'] ?? 0;
                                $optData['price_adjustment'] = $optData['price_adjustment'] ?? 0;
                                
                                $option = VariantOption::updateOrCreate(
                                    ['id' => $optData['id'] ?? null],
                                    $optData
                                );
                                $keepOptionIds[] = $option->id;
                            }
                            // Delete options that were removed
                            $variant->options()->whereNotIn('id', $keepOptionIds)->delete();
                        }
                    }

                    // Delete variants that were removed from the UI and not explicitly tracked
                    if (empty($data['deleted_variant_ids'])) {
                        $product->variants()->whereNotIn('id', $keepVariantIds)->delete();
                    }
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'product' => $product->fresh()->load(['categories', 'variants', 'images', 'brand']),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving product: ' . $e->getMessage(),
                'product' => null,
            ];
        }
    }

    public function getProductById(int $id): array
    {
        try {
            $product = Product::with(['brand', 'categories', 'variants.options', 'images'])->findOrFail($id);
            return [
                'status' => 'success',
                'product' => $product,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Product not found.',
                'product' => null,
            ];
        }
    }

    public function deleteProduct(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $product = Product::findOrFail($id);
                $product->delete();

                return [
                    'status' => 'success',
                    'message' => 'Product deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting product: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Duplicate a product including categories, variants, variant options and images.
     * New unique slug/SKU/barcode are generated and image files are physically copied,
     * so the duplicate is fully independent from the source product.
     */
    public function duplicateProduct(int $id, array $data): array
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $source = Product::with(['categories', 'variants.options', 'images'])->findOrFail($id);

                $name = trim((string) ($data['name'] ?? ''));
                if ($name === '') {
                    return [
                        'status' => 'error',
                        'message' => 'Product name is required.',
                        'product' => null,
                    ];
                }

                // Optional price override (applied to every variant's sale price)
                $price = isset($data['price']) && $data['price'] !== '' && $data['price'] !== null
                    ? (float) $data['price']
                    : null;

                $duplicate = Product::create([
                    'brand_id'          => $source->brand_id ?? $data['brand_id'] ?? null,
                    'category_id'       => $source->category_id ?? $data['category_id'] ?? null,
                    'navbar_item_id'    => $source->navbar_item_id,
                    'subnavbar_item_id' => $source->subnavbar_item_id,
                    'unit_id'           => $source->unit_id,
                    'size_id'           => $source->size_id,
                    'tax_rate_id'       => $source->tax_rate_id,
                    'name'              => $name,
                    'slug'              => $this->generateUniqueProductSlug($name),
                    'short_description' => $source->short_description,
                    'description'       => $source->description,
                    'product_type'      => $source->product_type,
                    'status'            => $source->status,
                    'visibility'        => $source->visibility,
                    'seo_title'         => $source->seo_title,
                    'seo_description'   => $source->seo_description,
                    'published_at'      => $source->published_at,
                    'is_homepage'       => false,
                ]);

// Categories (pivot table)
                $duplicate->categories()->sync($source->categories->pluck('id'));

                // Variants (+ their color/size options)
                $variantMap = [];
                foreach ($source->variants as $variant) {
                    $newVariant = $duplicate->variants()->create([
                        'sku'              => $this->generateUniqueVariantSku($variant->sku),
                        'barcode'          => (string) Str::uuid(),
                        'name'             => $variant->name,
                        'attributes'       => $variant->attributes,
                        'cost_price'       => $variant->cost_price,
                        'sale_price'       => $price !== null ? $price : $variant->sale_price,
                        'compare_at_price' => $variant->compare_at_price,
                        'weight_grams'     => $variant->weight_grams,
                        'length_mm'        => $variant->length_mm,
                        'width_mm'         => $variant->width_mm,
                        'height_mm'        => $variant->height_mm,
                        'track_inventory'  => $variant->track_inventory,
                        'allow_backorder'  => $variant->allow_backorder,
                        'status'           => $variant->status,
                    ]);
                    $variantMap[$variant->id] = $newVariant->id;

                    foreach ($variant->options as $option) {
                        VariantOption::create([
                            'product_variant_id' => $newVariant->id,
                            'color_name'         => $option->color_name,
                            'color_code'         => $option->color_code,
                            'image_url'          => $option->image_url ? $this->copyImageFile($option->image_url) : null,
                            'price_adjustment'   => $option->price_adjustment,
                            'stock'              => (int) ($option->stock ?? 0),
                            'sort_order'         => $option->sort_order,
                            'status'             => $option->status,
                        ]);
                    }
                }

                // Images (copy physical files so both products stay independent)
                foreach ($source->images as $image) {
                    ProductImage::create([
                        'product_id' => $duplicate->id,
                        'variant_id' => $image->variant_id ? ($variantMap[$image->variant_id] ?? null) : null,
                        'image_url'  => $this->copyImageFile($image->image_url),
                        'alt_text'   => $image->alt_text ?? $name,
                        'sort_order' => $image->sort_order,
                        'is_main'    => $image->is_main,
                    ]);
                }

                return [
                    'status' => 'success',
                    'message' => 'Product duplicated successfully.',
                    'product' => $duplicate->fresh()->load(['brand', 'categories', 'variants.options', 'images']),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error duplicating product: ' . $e->getMessage(),
                'product' => null,
            ];
        }
    }

    /**
     * Generate a unique product slug based on the given name.
     */
    private function generateUniqueProductSlug(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'product-' . Str::lower(Str::random(6));
        }

        $slug = $base;
        $counter = 2;
        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    /**
     * Generate a unique variant SKU derived from the original one.
     */
    private function generateUniqueVariantSku(?string $sku): string
    {
        $base = $sku && trim($sku) !== '' ? trim($sku) : ('PROD-' . Str::upper(Str::random(8)));

        $candidate = $base . '-COPY';
        $counter = 2;
        while (ProductVariant::withTrashed()->where('sku', $candidate)->exists()) {
            $candidate = $base . '-COPY-' . $counter++;
        }

        return Str::upper($candidate);
    }

    /**
     * Copy an image file so the duplicated product owns its own copy.
     * Returns the new relative storage path (or keeps the original reference
     * for external URLs / missing files).
     */
    private function copyImageFile(?string $imageUrl): ?string
    {
        if (!$imageUrl) {
            return null;
        }

        $path = $imageUrl;

        // External URLs that are not on local /storage are kept as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            if (!str_contains($path, '/storage/')) {
                return $path;
            }
            $parsed = parse_url($path, PHP_URL_PATH) ?? '';
            $path = ltrim($parsed, '/');
            if (str_starts_with($path, 'storage/')) {
                $path = substr($path, strlen('storage/'));
            }
        } else {
            $path = ltrim($path, '/');
            if (str_starts_with($path, 'storage/')) {
                $path = substr($path, strlen('storage/'));
            }
        }

        // Only uploaded files under products/ or categories/ are copied
        if (!str_starts_with($path, 'products/') && !str_starts_with($path, 'categories/')) {
            return $path;
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            return $path;
        }

        $newPath = 'products/' . Str::random(24) . '-' . basename($path);
        $disk->copy($path, $newPath);

        return $newPath;
    }

    public function getBrands(): Collection
    {
        return Brand::where('status', 'active')->orderBy('name')->get();
    }

    public function getCategories(): Collection
    {
        return Category::where('status', 'active')->orderBy('name')->get();
    }

    public function searchProducts(string $query, ?int $categoryId = null): array
    {
        try {
            $queryBuilder = Product::with(['brand', 'categories', 'images'])
                ->where('status', 'active')
                ->where('visibility', 'visible')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('short_description', 'LIKE', "%{$query}%")
                        ->orWhere('description', 'LIKE', "%{$query}%");
                });

            if ($categoryId && $categoryId > 0) {
                $queryBuilder->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('categories.id', $categoryId);
                });
            }

            $products = $queryBuilder->orderBy('order_column')->limit(20)->get();

            $formattedProducts = $products->map(function ($product) {
                $mainImage = $product->images->where('is_main', true)->first();
                $thumbnail = $mainImage ? asset('storage/' . $mainImage->image_url) : null;
                $category = $product->categories->first()?->name;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => (float) $product->variants->min('price') ?? 0,
                    'sale_price' => $product->variants->min('sale_price') ?? null,
                    'thumbnail' => $thumbnail,
                    'category' => $category,
                ];
            })->toArray();

            return [
                'status' => 'success',
                'message' => 'Products found',
                'data' => $formattedProducts,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error searching products: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    private function saveProductImages(Product $product, array $images): void
    {
        foreach ($images as $index => $image) {
            if ($image instanceof UploadedFile) {
                $fileName = Str::slug($product->name) . '-' . now()->format('YmdHis') . '-' . $index . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('products', $fileName, 'public');

                // First image is automatically set as main
                $isMain = !ProductImage::where('product_id', $product->id)->where('is_main', true)->exists() && $index === 0;

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => $path,
                    'alt_text' => $product->name,
                    'sort_order' => ProductImage::where('product_id', $product->id)->max('sort_order') + 1,
                    'is_main' => $isMain,
                ]);
            }
        }
    }

    private function removeProductImages(Product $product, array $imageIds): void
    {
        if (empty($imageIds)) {
            return;
        }

        $images = ProductImage::whereIn('id', $imageIds)->where('product_id', $product->id)->get();

        foreach ($images as $image) {
            $this->deleteImage($image->image_url);
            $image->delete();
        }

        // If the main image was deleted, assign main to the first remaining image
        if (!ProductImage::where('product_id', $product->id)->where('is_main', true)->exists()) {
            $firstImage = ProductImage::where('product_id', $product->id)->orderBy('sort_order')->first();
            if ($firstImage) {
                $firstImage->update(['is_main' => true]);
            }
        }
    }

    private function deleteImage(?string $imageUrl): void
    {
        if (!$imageUrl) {
            return;
        }

        // Handle URL-based paths (from seeders / legacy data)
        $path = parse_url($imageUrl, PHP_URL_PATH) ?: $imageUrl;

        // Strip leading /storage/ if present (legacy format)
        if (str_starts_with($path, '/storage/')) {
            $path = substr($path, strlen('/storage/'));
        }

        // Only delete if it's an uploaded file (no external URLs like https://via.placeholder.com)
        if (!str_starts_with($path, 'products/') && !str_starts_with($path, 'categories/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    /**
     * Reorder products based on the provided array of product IDs.
     * Each product's order_column is updated sequentially.
     */
    public function reorderProducts(array $productIds): array
    {
        try {
            return DB::transaction(function () use ($productIds) {
                foreach ($productIds as $index => $productId) {
                    Product::where('id', $productId)->update(['order_column' => $index + 1]);
                }

                return [
                    'status' => 'success',
                    'message' => 'Products reordered successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error reordering products: ' . $e->getMessage(),
            ];
        }
    }
}

