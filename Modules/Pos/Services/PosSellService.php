<?php

namespace Modules\Pos\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Account\Services\AccountTransactionService;
use Modules\Catalog\Models\Product;
use Modules\Identity\Models\User;
use Modules\Pos\Models\PosSale;
use Modules\Pos\Models\PosSaleItem;

class PosSellService
{
    /**
     * Search customers by phone number or name
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        try {
            $term = $request->get('term', '');

            $query = User::query()
                ->select('id', 'first_name', 'last_name', 'phone', 'email')
                ->where('status', 'active');

            if (! empty($term)) {
                $query->where(function ($q) use ($term) {
                    $q->where('phone', 'LIKE', "%{$term}%")
                        ->orWhere('first_name', 'LIKE', "%{$term}%")
                        ->orWhere('last_name', 'LIKE', "%{$term}%")
                        ->orWhere('email', 'LIKE', "%{$term}%");
                });
            }

            $customers = $query->orderBy('first_name')
                ->limit(20)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone ?? '-',
                        'email' => $user->email ?? '-',
                    ];
                });

            return ApiResponse::success($customers);
        } catch (\Exception $e) {
            return ApiResponse::error('Error searching customers: '.$e->getMessage(), 500);
        }
    }

    /**
     * Search products by name, SKU or barcode
     */
    public function searchProducts(Request $request): JsonResponse
    {
        try {
            $term = $request->get('term', '');

            $query = Product::query()
                ->with([
                    'variants' => fn ($q) => $q->with('options')->orderBy('id'),
                    'brand',
                    'unit',
                    'images',
                ])
                ->where('status', 'active');

            if (! empty($term)) {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'LIKE', "%{$term}%")
                        ->orWhereHas('variants', function ($vq) use ($term) {
                            $vq->where('sku', 'LIKE', "%{$term}%")
                                ->orWhereHas('options', fn ($oq) => $oq->where('sku', 'LIKE', "%{$term}%"));
                        });
                });
            }

            $products = $query->orderBy('name')
                ->limit(20)
                ->get()
                ->map(function ($product) {
                    $variants = $product->variants
                        ->filter(fn ($v) => (float) $v->sale_price > 0 || $v->options->isNotEmpty())
                        ->values()
                        ->map(fn ($variant) => $this->variantPayload($variant));

                    $prices = $this->collectPrices($variants);

                    return [
                        'id' => $product->id,
                        'name' => $this->normalizeText($product->name),
                        'product_type' => $product->product_type,
                        'brand' => $product->brand ? $this->normalizeText($product->brand->name) : '',
                        'unit' => $product->unit ? $product->unit->name : '',
                        'image' => $product->images->first()?->image_url ?? '',
                        'variants' => $variants,
                        'min_price' => $prices['min'],
                        'max_price' => $prices['max'],
                        'has_discount' => $prices['discounted'],
                    ];
                });

            return ApiResponse::success($products);
        } catch (\Exception $e) {
            return ApiResponse::error('Error searching products: '.$e->getMessage(), 500);
        }
    }

    /**
     * Build the POS-friendly payload for one product variant (with color
     * options). Every price is the final price after the variant's or the
     * color option's discount_percent is applied.
     */
    private function variantPayload($variant): array
    {
        $options = $variant->options
            ->filter(fn ($option) => empty($option->status) || $option->status !== 'inactive')
            ->values()
            ->map(fn ($option) => $this->optionPayload($option, $variant));

        return [
            'id' => $variant->id,
            'name' => $variant->name,
            'sku' => $variant->sku,
            'barcode' => $variant->barcode,
            'sale_price' => (float) $variant->sale_price,
            'discount_percent' => (float) $variant->discount_percent,
            'compare_at_price' => (float) $variant->compare_at_price,
            'price' => $this->finalPrice((float) $variant->sale_price, (float) $variant->discount_percent),
            'original_price' => (float) $variant->sale_price,
            'stock' => (int) $variant->stock,
            'options' => $options,
        ];
    }

    /**
     * Build the payload for a color option of a variant. Final price:
     * option sale_price if set, else variant sale_price + option price_adjustment.
     * Discount uses the option's own discount_percent when set, else the
     * variant's discount_percent.
     */
    private function optionPayload($option, $variant): array
    {
        $basePrice = (float) ($option->sale_price > 0
            ? $option->sale_price
            : (float) $variant->sale_price + (float) $option->price_adjustment);
        $discount = (float) ($option->discount_percent > 0 ? $option->discount_percent : $variant->discount_percent);

        return [
            'id' => $option->id,
            'variant_id' => $variant->id,
            'color_name' => $option->color_name,
            'color_code' => $option->color_code,
            'sku' => $option->sku,
            'barcode' => $option->barcode,
            'sale_price' => (float) $option->sale_price,
            'price_adjustment' => (float) $option->price_adjustment,
            'discount_percent' => (float) $option->discount_percent,
            'compare_at_price' => $option->compare_at_price > 0 ? (float) $option->compare_at_price : (float) $variant->compare_at_price,
            'price' => $this->finalPrice($basePrice, $discount),
            'original_price' => $basePrice,
            'stock' => (int) $option->stock,
        ];
    }

    /**
     * Apply discount_percent to a price and round to 2 decimals.
     */
    private function finalPrice(float $price, float $discountPercent): float
    {
        if ($price <= 0) {
            return 0;
        }

        if ($discountPercent > 0) {
            return round($price * (1 - $discountPercent / 100), 2);
        }

        return round($price, 2);
    }

    /**
     * Min/max price and whether any choice is discounted, used to show
     * "৳X - ৳Y" and a discount badge on the search result row.
     */
    private function collectPrices($variants): array
    {
        $prices = [];
        $discounted = false;

        foreach ($variants as $variant) {
            foreach ($variant['options'] as $option) {
                $prices[] = $option['price'];
                if ($option['discount_percent'] > 0) {
                    $discounted = true;
                }
            }

            if (empty($variant['options'])) {
                $prices[] = $variant['price'];
                if ($variant['discount_percent'] > 0) {
                    $discounted = true;
                }
            }
        }

        if (empty($prices)) {
            return ['min' => 0, 'max' => 0, 'discounted' => false];
        }

        return [
            'min' => min($prices),
            'max' => max($prices),
            'discounted' => $discounted,
        ];
    }

    /**
     * Process the POS sale and save it
     */
    public function processSale(Request $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $data = $request->validate([
                    'customer_id' => 'nullable|exists:users,id',
                    'register_id' => 'required|exists:pos_registers,id',
                    'shift_id' => 'required|exists:pos_shifts,id',
                    'items' => 'required|array|min:1',
                    'items.*.product_id' => 'required|exists:products,id',
                    'items.*.variant_id' => 'nullable|exists:product_variants,id',
                    'items.*.option_id' => 'nullable|integer',
                    'items.*.product_name' => 'required|string|max:220',
                    'items.*.variant_name' => 'nullable|string|max:220',
                    'items.*.color_name' => 'nullable|string|max:100',
                    'items.*.sku' => 'nullable|string|max:100',
                    'items.*.unit_price' => 'required|numeric|min:0',
                    'items.*.original_price' => 'nullable|numeric|min:0',
                    'items.*.discount_amount' => 'nullable|numeric|min:0',
                    'items.*.quantity' => 'required|numeric|min:0.01',
                    'items.*.subtotal' => 'required|numeric|min:0',
                    'items.*.total' => 'required|numeric|min:0',
                    'subtotal' => 'required|numeric|min:0',
                    'tax_amount' => 'nullable|numeric|min:0',
                    'discount_amount' => 'nullable|numeric|min:0',
                    'total' => 'required|numeric|min:0',
                    'cash_amount' => 'nullable|numeric|min:0',
                    'card_amount' => 'nullable|numeric|min:0',
                    'other_amount' => 'nullable|numeric|min:0',
                    'change_amount' => 'nullable|numeric|min:0',
                    'payment_status' => 'required|in:paid,partial,pending',
                    'notes' => 'nullable|string|max:500',
                ]);

                $receiptNumber = 'POS-'.strtoupper(uniqid());

                // Create the sale
                $sale = PosSale::create([
                    'register_id' => $data['register_id'],
                    'shift_id' => $data['shift_id'],
                    'user_id' => $data['customer_id'] ?? auth()->id(),
                    'receipt_number' => $receiptNumber,
                    'subtotal' => $data['subtotal'],
                    'tax_amount' => $data['tax_amount'] ?? 0,
                    'discount_amount' => $data['discount_amount'] ?? 0,
                    'total' => $data['total'],
                    'cash_amount' => $data['cash_amount'] ?? 0,
                    'card_amount' => $data['card_amount'] ?? 0,
                    'other_amount' => $data['other_amount'] ?? 0,
                    'change_amount' => $data['change_amount'] ?? 0,
                    'payment_status' => $data['payment_status'],
                    'status' => 'completed',
                    'notes' => $data['notes'] ?? null,
                ]);

                // Create sale items
                foreach ($data['items'] as $item) {
                    PosSaleItem::create([
                        'pos_sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['variant_id'] ?? null,
                        'variant_option_id' => $item['option_id'] ?? null,
                        'product_name' => $this->normalizeText($item['product_name']),
                        'sku' => $item['sku'] ?? null,
                        'unit_price' => $item['unit_price'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['subtotal'],
                        'tax_amount' => 0,
                        'discount_amount' => $item['discount_amount'] ?? 0,
                        'total' => $item['total'],
                    ]);
                }

                if (Schema::hasTable('account_transactions')) {
                    app(AccountTransactionService::class)->postPosSale($sale->fresh(['items.product.variants', 'register.store']));
                }

                return ApiResponse::success([
                    'sale' => $sale->fresh()->load(['items', 'register', 'shift']),
                    'receipt' => [
                        'receipt_number' => $receiptNumber,
                        'total' => $data['total'],
                        'items_count' => count($data['items']),
                    ],
                ], 'Sale completed successfully!');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error processing sale: '.$e->getMessage(), 500);
        }
    }

    /**
     * Decode HTML entities repeatedly until the string stops changing.
     *
     * Product names sometimes get saved multiple times through HTML-escaped
     * form values ("&" -> "&amp;" -> "&amp;amp;" ...). This normalises any
     * depth of encoding back to the real text, so the POS shows and stores
     * clean names instead of "&amp;amp;amp;".
     */
    private function normalizeText(string $value): string
    {
        for ($i = 0; $i < 5 && str_contains($value, '&'); $i++) {
            $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if ($decoded === $value) {
                break;
            }

            $value = $decoded;
        }

        return trim($value);
    }

    /**
     * Get last few sales for the current register/shift
     */
    public function getRecentSales(Request $request): JsonResponse
    {
        try {
            $registerId = $request->get('register_id');
            $query = PosSale::with(['items', 'user'])
                ->orderByDesc('created_at')
                ->limit(10);

            if ($registerId) {
                $query->where('register_id', $registerId);
            }

            $sales = $query->get()->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'receipt_number' => $sale->receipt_number,
                    'total' => number_format($sale->total, 2),
                    'items_count' => $sale->items->count(),
                    'customer' => $sale->user ? $sale->user->name : 'Walk-in',
                    'payment_status' => $sale->payment_status,
                    'created_at' => $sale->created_at->format('h:i A'),
                ];
            });

            return ApiResponse::success($sales);
        } catch (\Exception $e) {
            return ApiResponse::error('Error fetching recent sales: '.$e->getMessage(), 500);
        }
    }
}
