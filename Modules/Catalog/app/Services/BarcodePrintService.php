<?php

namespace Modules\Catalog\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodePrintService
{
    public function searchProducts(Request $request): JsonResponse
    {
        $query = Product::forCurrentStore()->with(['brand', 'categories', 'variants' => function ($q) {
            $q->where('status', 'active');
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        return ApiResponse::success(
            $query->where('status', 'active')->limit(50)->get(),
            'Products retrieved successfully.'
        );
    }

    public function autocomplete(string $search): JsonResponse
    {
        if (strlen($search) < 1) {
            return ApiResponse::success([], 'Products retrieved successfully.');
        }

        $products = Product::forCurrentStore()
            ->where('status', 'active')
            ->where('name', 'like', "%{$search}%")
            ->with('brand')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'brand' => $p->brand?->name,
                    'label' => $p->name.($p->brand ? " ({$p->brand->name})" : ''),
                ];
            });

        return ApiResponse::success($products, 'Suggestions retrieved successfully.');
    }

    public function getProductVariants(int $productId): JsonResponse
    {
        return ApiResponse::success(
            Product::forCurrentStore()->with(['brand', 'variants' => function ($q) {
                $q->where('status', 'active');
            }])->findOrFail($productId),
            'Product variants retrieved successfully.'
        );
    }

    /**
     * Build printable barcode label rows for the requested variants/copies.
     */
    public function generateLabels(array $variantItems): array
    {
        $variantIds = collect($variantItems)->pluck('variant_id');
        $variants = ProductVariant::whereIn('id', $variantIds)
            ->whereHas('product', fn ($q) => $q->forCurrentStore())
            ->with('product')
            ->get()
            ->keyBy('id');

        $generator = new BarcodeGeneratorSVG;
        $barcodes = [];

        foreach ($variantItems as $item) {
            $variant = $variants->get($item['variant_id']);
            if (! $variant || ! $variant->product) {
                continue;
            }

            $barcodeValue = $variant->barcode ?? $variant->sku ?? (string) $variant->id;

            for ($i = 0; $i < $item['copies']; $i++) {
                try {
                    $barcodeSvg = $generator->getBarcode($barcodeValue, $generator::TYPE_CODE_128);
                } catch (\Exception) {
                    $barcodeSvg = '<svg><text>Error</text></svg>';
                }

                $barcodes[] = [
                    'name' => $variant->product->name.' - '.$variant->name,
                    'barcode_value' => $barcodeValue,
                    'sku' => $variant->sku,
                    'barcode_svg' => $barcodeSvg,
                ];
            }
        }

        return $barcodes;
    }
}
