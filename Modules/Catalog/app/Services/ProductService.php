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
            ])
            ->orderByDesc('products.created_at');

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
                    // Handle "new_0", "new_1" etc. - refers to index in uploaded images array
                    if (is_string($mainImageId) && str_starts_with($mainImageId, 'new_')) {
                        $imageIndex = (int) substr($mainImageId, 4);
                        $uploadedImages = $data['images'] ?? [];
                        if (isset($uploadedImages[$imageIndex]) && $uploadedImages[$imageIndex] instanceof \Illuminate\Http\UploadedFile) {
                            // The first uploaded image will be auto-set as main by saveProductImages
                            // But if user selected a different one, we need to adjust after saving
                            // We'll handle this by marking the correct one after all images are saved
                            $allImages = ProductImage::where('product_id', $product->id)
                                ->orderBy('id')
                                ->get();
                            // The newly uploaded images are appended at the end
                            // Count existing images before this batch
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

            $products = $queryBuilder->limit(20)->get();

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
}
