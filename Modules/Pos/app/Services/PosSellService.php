<?php

namespace Modules\Pos\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\Account\Services\AccountTransactionService;
use Modules\Cart\Models\CampaignProduct;
use Modules\Cart\Services\CampaignPricingService;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Modules\Catalog\Models\VariantOption;
use Modules\Identity\Models\User;
use Modules\Pos\Models\PosSale;
use Modules\Pos\Models\PosSaleItem;

class PosSellService
{
    public function __construct(private CampaignPricingService $campaignPricing) {}

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
                        'campaign' => $this->productCampaign($product),
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

        // CampaignPricingService is the single source of truth:
        // live campaign wins, otherwise variant discount applies.
        $variantPricing = $this->campaignPricing->finalPriceFor($variant);
        $variantBase = (float) $variant->sale_price;
        $variantPrice = (float) $variantPricing['unit_price'];
        $variantCampaign = $variantPricing['campaign'];

        return [
            'id' => $variant->id,
            'name' => $variant->name,
            'sku' => $variant->sku,
            'barcode' => $variant->barcode,
            'sale_price' => $variantBase,
            'discount_percent' => (float) $variant->discount_percent,
            'compare_at_price' => (float) $variant->compare_at_price,
            'price' => round(max(0, $variantPrice), 2),
            'original_price' => $variantBase,
            'stock' => (int) $variant->stock,
            'options' => $options,
            'campaign' => $variantCampaign ? [
                'id' => $variantCampaign->id,
                'name' => $variantCampaign->name,
                'source' => $variantPricing['source'],
            ] : null,
        ];
    }

    private function optionPayload($option, $variant): array
    {
        // CampaignPricingService is the single source of truth.
        $pricing = $this->campaignPricing->finalPriceFor($variant, $option);
        $basePrice = (float) ($option->sale_price > 0
            ? $option->sale_price
            : (float) $variant->sale_price + (float) $option->price_adjustment);
        $optionPrice = (float) $pricing['unit_price'];
        $campaign = $pricing['campaign'];

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
            'price' => round(max(0, $optionPrice), 2),
            'original_price' => $basePrice,
            'stock' => (int) $option->stock,
            'campaign' => $campaign ? [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'source' => $pricing['source'],
            ] : null,
        ];
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
                if ($option['discount_percent'] > 0 || ! empty($option['campaign'])) {
                    $discounted = true;
                }
            }

            if (empty($variant['options'])) {
                $prices[] = $variant['price'];
                if ($variant['discount_percent'] > 0 || ! empty($variant['campaign'])) {
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
     * Process and complete a POS sale.
     *
     * Server-side price resolution: for each item we load the variant/option
     * and run it through CampaignPricingService so the final unit price,
     * discount source (variant vs campaign) and campaign_id are always
     * authoritative — never trusted from the client payload.
     */
    public function processSale(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'register_id' => 'required|exists:pos_registers,id',
                'shift_id' => 'required|exists:pos_shifts,id',
                'customer_id' => 'nullable|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.variant_id' => 'required|exists:product_variants,id',
                'items.*.option_id' => 'nullable|exists:variant_options,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.sku' => 'nullable|string',
                'items.*.product_name' => 'nullable|string',
                'payment_status' => 'required|in:paid,partial,pending',
                'notes' => 'nullable|string|max:500',
            ]);

            return DB::transaction(function () use ($data) {
                $subtotal = 0;
                $totalDiscount = 0;
                $items = [];

                foreach ($data['items'] as $item) {
                    $variant = ProductVariant::findOrFail($item['variant_id']);
                    $option = ! empty($item['option_id'])
                        ? VariantOption::find($item['option_id'])
                        : null;

                    $pricing = $this->campaignPricing->finalPriceFor($variant, $option);
                    $unitPrice = (float) $pricing['unit_price'];
                    $campaign = $pricing['campaign'];
                    $discountSource = $campaign ? 'campaign' : 'variant';

                    $basePrice = $option !== null
                        ? ($option->sale_price !== null
                            ? (float) $option->sale_price
                            : (float) $variant->sale_price + (float) ($option->price_adjustment ?? 0))
                        : (float) $variant->sale_price;

                    $discountPerUnit = round($basePrice - $unitPrice, 2);
                    $quantity = (float) $item['quantity'];
                    $itemSubtotal = round($unitPrice * $quantity, 2);
                    $itemDiscount = round($discountPerUnit * $quantity, 2);

                    $subtotal += $itemSubtotal;
                    $totalDiscount += $itemDiscount;

                    $items[] = [
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['variant_id'],
                        'variant_option_id' => $item['option_id'] ?? null,
                        'campaign_id' => $campaign ? $campaign->id : null,
                        'discount_source' => $discountSource,
                        'product_name' => $this->normalizeText($item['product_name'] ?? $variant->name),
                        'sku' => $item['sku'] ?? ($option->sku ?? $variant->sku),
                        'unit_price' => $unitPrice,
                        'quantity' => $quantity,
                        'subtotal' => $itemSubtotal,
                        'tax_amount' => 0,
                        'discount_amount' => $itemDiscount,
                        'total' => $itemSubtotal,
                    ];
                }

                $total = round($subtotal, 2);

                $sale = PosSale::create([
                    'register_id' => $data['register_id'],
                    'shift_id' => $data['shift_id'],
                    'user_id' => $data['customer_id'] ?? auth()->id(),
                    'receipt_number' => 'POS-'.strtoupper(uniqid()),
                    'subtotal' => $subtotal,
                    'tax_amount' => 0,
                    'discount_amount' => $totalDiscount,
                    'total' => $total,
                    'cash_amount' => $data['cash_amount'] ?? $total,
                    'card_amount' => $data['card_amount'] ?? 0,
                    'other_amount' => $data['other_amount'] ?? 0,
                    'change_amount' => $data['change_amount'] ?? 0,
                    'payment_status' => $data['payment_status'],
                    'status' => 'completed',
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($items as $item) {
                    $item['pos_sale_id'] = $sale->id;
                    PosSaleItem::create($item);
                }

                if (Schema::hasTable('account_transactions')) {
                    app(AccountTransactionService::class)
                        ->postPosSale($sale->fresh(['items.product.variants', 'register.store']));
                }

                return ApiResponse::success([
                    'sale_id' => $sale->id,
                    'receipt_number' => $sale->receipt_number,
                    'total' => number_format($sale->total, 2),
                    'items_count' => count($items),
                ], 'Sale completed successfully.');
            });
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation error: '.$e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Error processing sale: '.$e->getMessage(), 500);
        }
    }

    /**
     * Return the live campaign that covers this product (highest priority),
     * or null when no active campaign applies. Used by the search result
     * row to show a CAMPAIGN badge.
     */
    private function productCampaign($product): ?array
    {
        $offer = CampaignProduct::query()
            ->with('campaign')
            ->where('product_id', $product->id)
            ->whereHas('campaign', fn ($q) => $q->live())
            ->get()
            ->sortByDesc(fn ($item) => $item->campaign->priority)
            ->first();

        if (! $offer) {
            return null;
        }

        return [
            'id' => $offer->campaign->id,
            'name' => $offer->campaign->name,
            'discount_type' => $offer->discount_type,
            'discount_value' => (float) $offer->discount_value,
        ];
    }

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
