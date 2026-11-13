<?php

namespace Modules\Frontend\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Frontend\Http\Resources\ProductSearchResource;
use Modules\Frontend\Services\GeminiChatIntentService;
use Modules\Frontend\Services\CentralProductSearchService;
use Modules\Order\Models\Order;

class ChatController extends Controller
{
    public function orderStatus(Request $request): JsonResponse
    {
        $input = $request->validate([
            'order_number' => ['nullable', 'string', 'max:80'],
        ]);

        $query = Order::query()
            ->where('user_id', $request->user()->id)
            ->withCount('items')
            ->with([
                'shipments' => fn ($builder) => $builder->latest('created_at'),
                'deliveries' => fn ($builder) => $builder->latest('created_at'),
            ])
            ->latest('placed_at');

        if (! empty($input['order_number'])) {
            $query->where('order_number', $input['order_number']);
        }

        $orders = $query->limit(5)->get()->map(function (Order $order) {
            $shipment = $order->shipments->first();
            $delivery = $order->deliveries->first();
            $status = $shipment?->status ?? match ($delivery?->status) {
                'assigned' => 'confirmed',
                'picked' => 'out_for_delivery',
                'delivered' => 'delivered',
                default => $order->status,
            };

            return [
                'order_number' => $order->order_number,
                'status' => $status,
                'placed_at' => $order->placed_at?->toIso8601String() ?? $order->created_at?->toIso8601String(),
                'items_count' => (int) $order->items_count,
                'grand_total' => (float) $order->grand_total,
                'currency_code' => $order->currency_code,
                'tracking_number' => $shipment?->tracking_number,
                'eta_at' => $shipment?->eta_at?->toIso8601String(),
            ];
        })->values();

        $reply = $orders->isEmpty()
            ? 'আপনার account-এ এই order-টি খুঁজে পেলাম না। Order number ঠিক আছে কি না দেখুন।'
            : ($input['order_number'] ?? null
                ? 'আপনার order-এর সর্বশেষ status নিচে দিলাম:'
                : 'আপনার সাম্প্রতিক order-গুলোর status নিচে দিলাম:');

        return ApiResponse::success([
            'reply' => $reply,
            'orders' => $orders,
        ]);
    }

    public function message(
        Request $request,
        GeminiChatIntentService $gemini,
        CentralProductSearchService $productSearch,
    ): JsonResponse {
        $input = $request->validate([
            'message' => ['required', 'string', 'max:600'],
            'history' => ['sometimes', 'array', 'max:6'],
            'history.*.role' => ['required', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:600'],
        ]);

        $message = trim($input['message']);
        $knowledgeBaseConfigured = trim((string) config('chatbot.knowledge_base', '')) !== '';

        try {
            $intent = $gemini->understand($message, $input['history'] ?? []);
        } catch (\Throwable) {
            // Product search remains useful if Gemini is out of quota or unavailable.
            $intent = [
                'intent' => 'product_search',
                'query' => $message,
                'category' => '',
                'reply' => '',
            ];
        }

        if ($intent['intent'] === 'faq' || $intent['intent'] === 'other') {
            if ($intent['intent'] === 'faq' && ! $knowledgeBaseConfigured) {
                return ApiResponse::success([
                    'reply' => 'এই তথ্যটি এখনো আমাদের chatbot-এ যোগ করা হয়নি। পণ্য খুঁজতে চাইলে পণ্যের নাম বা category লিখুন।',
                    'products' => [],
                ]);
            }

            return ApiResponse::success([
                'reply' => $intent['reply'] ?: 'কী ধরনের পণ্য খুঁজছেন বলুন, আমি খুঁজে দিচ্ছি।',
                'products' => [],
            ]);
        }

        $category = $this->findCategory($intent['category'], $message);
        $query = trim($intent['query']);
        $browseCategory = $category && (
            $query === ''
            || Str::lower($query) === Str::lower($category->name)
            || Str::lower($query) === Str::lower($category->slug)
            || Str::lower($query) === Str::lower($message)
        );

        if ($browseCategory) {
            $products = Product::query()
                ->whereNull('products.store_id')
                ->where('status', 'active')
                ->where('visibility', 'public')
                ->whereHas('categories', fn ($builder) => $builder
                    ->where('categories.id', $category->id)
                    ->orWhere('categories.parent_id', $category->id))
                ->with([
                    'images',
                    'unit',
                    'variants' => fn ($builder) => $builder->where('status', 'active')->with(['inventoryStocks', 'options.inventoryStocks']),
                ])
                ->orderBy('order_column')
                ->limit(8)
                ->get();
            $suggestion = null;
        } elseif ($query !== '') {
            $result = $productSearch->search($query, 8, $category?->id);
            $products = $result['products'];
            $suggestion = $result['suggestion'];
        } else {
            $products = Product::query()
                ->whereNull('products.store_id')
                ->where('status', 'active')
                ->where('visibility', 'public')
                ->with([
                    'images',
                    'unit',
                    'variants' => fn ($builder) => $builder->where('status', 'active')->with(['inventoryStocks', 'options.inventoryStocks']),
                ])
                ->orderBy('order_column')
                ->limit(8)
                ->get();
            $suggestion = null;
        }

        $items = ProductSearchResource::collection($products)->resolve($request);
        $reply = match (true) {
            count($items) > 0 && $category !== null => "{$category->name} category থেকে এই পণ্যগুলো পেয়েছি:",
            count($items) > 0 => 'আপনার কথামতো এই পণ্যগুলো পেয়েছি:',
            $suggestion !== null => "এই নামের কাছাকাছি পণ্য পেয়েছি: {$suggestion}",
            default => 'দুঃখিত, মিল আছে এমন পণ্য পাইনি। অন্য নাম বা category দিয়ে খুঁজে দেখবেন?',
        };

        return ApiResponse::success([
            'reply' => $reply,
            'products' => $items,
            'suggestion' => $suggestion,
        ]);
    }

    private function findCategory(string $requestedCategory, string $message): ?Category
    {
        $requestedCategory = trim($requestedCategory);

        if ($requestedCategory !== '') {
            $category = Category::query()
                ->whereNull('store_id')
                ->where('status', 'active')
                ->where(fn ($builder) => $builder
                    ->where('name', 'like', '%'.mb_substr($requestedCategory, 0, 80).'%')
                    ->orWhere('slug', 'like', '%'.Str::slug($requestedCategory).'%'))
                ->first();

            if ($category) {
                return $category;
            }
        }

        // A safe local fallback for broad category requests when Gemini is down.
        $normalizedMessage = Str::lower($message);

        return Category::query()
            ->whereNull('store_id')
            ->where('status', 'active')
            ->get(['id', 'parent_id', 'name', 'slug', 'status'])
            ->first(fn (Category $category) => Str::contains($normalizedMessage, Str::lower($category->name))
                || Str::contains($normalizedMessage, Str::lower($category->slug)));
    }
}
