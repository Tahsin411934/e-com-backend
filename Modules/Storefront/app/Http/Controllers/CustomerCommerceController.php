<?php

namespace Modules\Storefront\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\Wishlist;
use Modules\Cart\Services\CampaignPricingService;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Modules\Catalog\Models\VariantOption;
use Modules\Order\Models\Delivery;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Reviews\Models\ProductReview;
use Modules\Store\Support\CurrentStore;

/** Customer commerce never uses legacy users.id ownership. */
class CustomerCommerceController extends Controller
{
    public function __construct(private CampaignPricingService $pricing) {}

    private function products()
    {
        abort_if(CurrentStore::id() === null, 404);

        return Product::where('store_id', CurrentStore::id())->where('status', 'active')->where('visibility', 'public');
    }

    private function cart(Request $request)
    {
        return Cart::where('customer_id', $request->user()->id)
            ->where('store_id', CurrentStore::id())->where('status', 'active');
    }

    /** Validate every ID and relationship before saving anything. */
    private function resolveItems(array $items): array
    {
        $resolved = [];
        foreach ($items as $item) {
            $product = $this->products()->findOrFail($item['product_id']);
            $variant = ProductVariant::where('product_id', $product->id)->where('status', 'active')
                ->when($item['variant_id'] ?? null, fn ($q, $id) => $q->whereKey($id))->firstOrFail();
            $option = empty($item['variant_option_id']) ? null : VariantOption::where('product_variant_id', $variant->id)->where('status', 'active')->findOrFail($item['variant_option_id']);
            $key = $variant->id.':'.($option?->id ?? '');
            abort_if(isset($resolved[$key]), 422, 'Duplicate cart item.');
            $resolved[$key] = [
                'product' => $product, 'variant' => $variant, 'option' => $option,
                'quantity' => (int) $item['quantity'],
                'unit_price' => $this->pricing->finalPriceFor($variant, $option)['unit_price'],
            ];
        }

        return array_values($resolved);
    }

    private function itemRules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.variant_option_id' => ['nullable', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function syncCart(Request $request)
    {
        $data = $request->validate(array_replace($this->itemRules(), ['items' => ['present', 'array', 'max:100']]));
        $items = $this->resolveItems($data['items']);

        return DB::transaction(function () use ($request, $items) {
            // The customer row serializes concurrent sync/checkout operations.
            $request->user()->newQuery()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $cart = $this->cart($request)->first() ?? Cart::create([
                'customer_id' => $request->user()->id, 'store_id' => CurrentStore::id(), 'status' => 'active',
            ]);
            $cart->items()->forceDelete();
            foreach ($items as $item) {
                $cart->items()->create([
                    'variant_id' => $item['variant']->id, 'variant_option_id' => $item['option']?->id,
                    'quantity' => $item['quantity'], 'unit_price' => $item['unit_price'],
                ]);
            }

            return ApiResponse::success($cart->fresh()->load('items'), 'Cart synced successfully.');
        });
    }

    public function checkout(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $request->user()->newQuery()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $cart = $this->cart($request)->with('items.variant')->lockForUpdate()->firstOrFail();
            abort_if($cart->items->isEmpty(), 422, 'Cart is empty.');
            $items = $cart->items->map(fn ($item) => [
                'product_id' => $item->variant?->product_id, 'variant_id' => $item->variant_id,
                'variant_option_id' => $item->variant_option_id, 'quantity' => $item->quantity,
            ])->all();
            $response = $this->placeOrder($request, $items, $request->user()->id);
            $cart->items()->forceDelete();
            $cart->update(['status' => 'converted']);

            return $response;
        });
    }

    public function guestCheckout(Request $request)
    {
        $data = $request->validate($this->itemRules() + ['customer_name' => ['required', 'string', 'max:191']]);

        return DB::transaction(fn () => $this->placeOrder($request, $data['items']));
    }

    private function placeOrder(Request $request, array $items, ?int $customerId = null): array
    {
        $data = $request->validate([
            'delivery_address' => ['required', 'string', 'max:191'],
            'delivery_city' => ['required', 'string', 'max:191'],
            'delivery_phone' => ['required', 'string', 'max:40'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $items = $this->resolveItems($items);
        abort_if(empty($items), 422, 'Cart is empty.');
        $subtotal = round(collect($items)->sum(fn ($item) => $item['unit_price'] * $item['quantity']), 2);
        $shipping = round(collect($items)->max(fn ($item) => (float) ($item['product']->delivery_charge ?? Product::DEFAULT_DELIVERY_CHARGE)), 2);
        $order = Order::create([
            'order_number' => 'ORD-'.Str::upper((string) Str::ulid()), 'customer_id' => $customerId,
            'user_id' => null, 'store_id' => CurrentStore::id(), 'source' => 'web',
            'status' => 'pending', 'payment_status' => 'unpaid', 'fulfillment_status' => 'unfulfilled',
            'currency_code' => CurrentStore::store()->currency_code ?: 'BDT',
            'subtotal' => $subtotal, 'discount_total' => 0, 'tax_total' => 0,
            'shipping_total' => $shipping, 'grand_total' => $subtotal + $shipping,
            'customer_name' => $customerId ? $request->user()->name : $request->input('customer_name'),
            'customer_note' => $data['delivery_notes'] ?? $data['notes'] ?? null, 'placed_at' => now(),
        ]);
        Delivery::create([
            'order_id' => $order->id, 'user_id' => null, 'status' => 'pending',
            'delivery_address' => $data['delivery_address'], 'delivery_city' => $data['delivery_city'],
            'delivery_phone' => $data['delivery_phone'], 'delivery_notes' => $order->customer_note,
        ]);
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id, 'product_id' => $item['product']->id, 'variant_id' => $item['variant']->id,
                'sku' => $item['option']?->sku ?? $item['variant']->sku,
                'product_name' => $item['product']->name,
                'variant_name' => $item['variant']->name.($item['option']?->color_name ? ' - '.$item['option']->color_name : ''),
                'quantity' => $item['quantity'], 'unit_price' => $item['unit_price'],
                'discount_total' => 0, 'tax_total' => 0, 'line_total' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return ['status' => 'success', 'message' => 'Order placed successfully.',
            'orders' => [$order->fresh()->load('items')], 'order_count' => 1,
            'total_amount' => $subtotal + $shipping, 'split_by_store' => false];
    }

    private function wishlistQuery(Request $request)
    {
        return Wishlist::where('customer_id', $request->user()->id)
            ->whereHas('product', fn ($q) => $q->where('store_id', CurrentStore::id())->where('status', 'active')->where('visibility', 'public'));
    }

    public function wishlist(Request $request)
    {
        $pricing = app(\Modules\Frontend\Services\ProductPricingService::class);
        $items = $this->wishlistQuery($request)->with(['product.images', 'product.variants' => fn ($q) => $q->where('status', 'active')])->latest()->get();
        $wishlist = $items->map(function ($item) use ($pricing) {
            $product = $item->product;
            $image = $product->images->firstWhere('is_main', true) ?? $product->images->first();

            return ['id' => $item->id, 'product_id' => $product->id, 'created_at' => $item->created_at,
                'product' => ['id' => $product->id, 'name' => $product->name, 'slug' => $product->slug,
                    'main_image' => $image?->image_url ? asset('storage/'.preg_replace('#^(?:storage/)+#', '', ltrim($image->image_url, '/'))) : null,
                    ...$pricing->priceInfo($product)]];
        });

        return response()->json(['status' => 'success', 'wishlist' => $wishlist]);
    }

    public function toggleWishlist(Request $request)
    {
        $data = $request->validate(['product_id' => ['required', 'integer']]);
        $product = $this->products()->findOrFail($data['product_id']);

        return DB::transaction(function () use ($request, $product) {
            $request->user()->newQuery()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $item = Wishlist::withTrashed()->where('customer_id', $request->user()->id)->where('product_id', $product->id)->first();
            if ($item && ! $item->trashed()) {
                $item->delete();
                return ApiResponse::success('removed', 'Removed from wishlist.');
            }
            $item ? $item->restore() : Wishlist::create(['customer_id' => $request->user()->id, 'product_id' => $product->id]);

            return ApiResponse::success('added', 'Added to wishlist.');
        });
    }

    public function removeWishlist(Request $request, int $productId)
    {
        $this->wishlistQuery($request)->where('product_id', $productId)->firstOrFail()->delete();

        return ApiResponse::success(null, 'Wishlist item removed successfully.');
    }

    public function reviews(int $productId)
    {
        $this->products()->findOrFail($productId);
        $reviews = ProductReview::where('product_id', $productId)->where('status', 'approved')
            ->where(fn ($q) => $q->whereNull('customer_id')->orWhereHas('customer', fn ($customer) => $customer->where('store_id', CurrentStore::id())))
            ->with(['customer', 'user'])->latest()->get();

        return ApiResponse::success($reviews->map(fn ($review) => $this->formatReview($review)));
    }

    public function submitReview(Request $request, int $productId)
    {
        $this->products()->findOrFail($productId);
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'max:255'], 'body' => ['required', 'string', 'max:5000'],
        ]);

        return DB::transaction(function () use ($request, $productId, $data) {
            $request->user()->newQuery()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            abort_if(ProductReview::where('customer_id', $request->user()->id)->where('product_id', $productId)->exists(), 422, 'You have already reviewed this product.');
            $review = ProductReview::create($data + ['customer_id' => $request->user()->id, 'product_id' => $productId, 'status' => 'pending', 'is_verified_purchase' => false]);

            return ApiResponse::created($this->formatReview($review->load('customer')), 'Review submitted for approval.');
        });
    }

    private function formatReview(ProductReview $review): array
    {
        return ['id' => $review->id, 'user_name' => $review->customer?->name ?? $review->user?->name ?? 'Customer',
            'rating' => $review->rating, 'title' => $review->title, 'body' => $review->body,
            'is_verified_purchase' => $review->is_verified_purchase, 'created_at' => $review->created_at?->diffForHumans()];
    }
}
