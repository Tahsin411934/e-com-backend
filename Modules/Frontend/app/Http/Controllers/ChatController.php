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
use Modules\Frontend\Services\GeminiQuotaExceededException;
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
        $geminiQuotaExceeded = false;

        if ($this->isWhatsappContactRequest($message)) {
            return ApiResponse::success([
                'reply' => 'অবশ্যই—নিচের WhatsApp বাটনে চাপ দিয়ে আমাদের সঙ্গে সরাসরি যোগাযোগ করুন। নম্বর: 01856533432',
                'products' => [],
                'suggestion' => null,
                'support' => $this->whatsappSupport('পণ্য বা অন্য সহায়তার জন্য WhatsApp-এ আমাদের নক করুন।'),
            ]);
        }

        try {
            $intent = $gemini->understand($message, $input['history'] ?? []);
        } catch (GeminiQuotaExceededException) {
            $geminiQuotaExceeded = true;
            $intent = [
                'intent' => 'product_search',
                'query' => $message,
                'category' => '',
                'reply' => '',
            ];
        } catch (\Throwable) {
            // Product search remains useful if Gemini is out of quota or unavailable.
            $intent = [
                'intent' => 'product_search',
                'query' => $message,
                'category' => '',
                'reply' => '',
            ];
        }

        // Catch common spoken/typed search commands locally so an imperfect
        // intent response cannot turn a clear product search into a FAQ.
        if ($intent['intent'] !== 'product_search' && $this->isProductSearchRequest($message)) {
            $localQueries = $this->buildSearchQueries('', $message);
            $intent = [
                'intent' => 'product_search',
                'query' => $localQueries[0] ?? $message,
                'category' => '',
                'reply' => '',
            ];
        }

        if ($intent['intent'] === 'faq' || $intent['intent'] === 'other') {
            if ($intent['intent'] === 'faq' && ! $knowledgeBaseConfigured) {
                return ApiResponse::success([
                    'reply' => 'এই তথ্যটি এখনো আমাদের chatbot-এ যোগ করা হয়নি। নিচের WhatsApp বাটনে চাপ দিয়ে আমাদের সঙ্গে যোগাযোগ করুন।',
                    'products' => [],
                    'support' => $this->whatsappSupport('আমাদের টিম আপনার প্রশ্নের উত্তর দিতে সাহায্য করবে।'),
                ]);
            }

            return ApiResponse::success([
                'reply' => $intent['reply'] ?: 'কী ধরনের পণ্য খুঁজছেন বলুন, আমি খুঁজে দিচ্ছি।',
                'products' => [],
            ]);
        }

        $category = $this->findCategory($intent['category'], $message);
        $query = trim($intent['query']);
        $searchQueries = $this->buildSearchQueries($query, $message);
        if ($query === '' && $searchQueries !== []) {
            $query = $searchQueries[0];
        }
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
            $result = null;
            foreach ($searchQueries ?: [$query] as $searchQuery) {
                $result = $productSearch->search($searchQuery, 8, $category?->id);
                if ($result['products']->isNotEmpty()) {
                    break;
                }
            }
            $result ??= $productSearch->search($query, 8, $category?->id);
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
            default => 'দুঃখিত, ক্যাটালগে আপনার চাওয়া পণ্যটি খুঁজে পাইনি। নিচের WhatsApp বাটনে চাপ দিয়ে আমাদের সঙ্গে যোগাযোগ করুন—আমরা খুঁজে পেতে সাহায্য করব।',
        };

        return ApiResponse::success([
            'reply' => $reply,
            'products' => $items,
            'suggestion' => $suggestion,
            'support' => $geminiQuotaExceeded
                ? $this->whatsappSupport('আমাদের AI সহকারীর সাময়িক ব্যবহারসীমা পূর্ণ হয়েছে। দ্রুত সহায়তার জন্য WhatsApp-এ আমাদের নক করুন।')
                : (count($items) === 0 ? $this->whatsappSupport('পণ্যটি খুঁজে পেতে বা বিকল্প জানতে WhatsApp-এ আমাদের নক করুন।') : null),
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

    /**
     * Use the customer's original wording as a resilient fallback when Gemini
     * returns an empty or overly broad search phrase.
     *
     * @return array<int, string>
     */
    private function buildSearchQueries(string $intentQuery, string $message): array
    {
        $cleanMessage = preg_replace(
            '/(?:দেখিয়ে দিন|দেখিয়ে দিন|দেখান|দেখাও|দেখতে চাই|খুঁজে দিন|খুঁজে দাও|খুঁজুন|খোঁজ দিন|খোঁজ দাও|আমাকে|একটু|দাও|দিন|দে|চাই|লাগবে|পণ্যগুলো|পণ্য|প্রোডাক্টগুলো|প্রোডাক্ট|প্লিজ)/u',
            ' ',
            $message
        ) ?? $message;
        $cleanMessage = preg_replace(
            '/(?<![\pL\pN])(?:please|can you|could you|show me|find|search(?: for)?|looking for|i want|i need|products?|items?|available|khujte dao|khuje dao|khujte den|khuje den|khuj(e|te) dao|khuj(e|te) den|dekhao|dekhaw|dekhte chai|show koro|sob|shob|ki ki|ase|ache|dao|den|lagbe|chai)(?![\pL\pN])/iu',
            ' ',
            $cleanMessage
        ) ?? $cleanMessage;
        $cleanMessage = trim(preg_replace('/\s+/u', ' ', $cleanMessage) ?? $cleanMessage, " \t\n\r\0\x0B,.!?।");

        $queries = array_filter([$intentQuery, $cleanMessage], fn (string $value) => trim($value) !== '');

        foreach ($queries as $candidate) {
            if (preg_match('/(?<![\pL\pN])(?:chori|churi|churri)(?![\pL\pN])/iu', $candidate)) {
                array_push($queries, 'churi', 'chori', 'চুড়ি', 'চুরি', 'bangles', 'bangle');
                break;
            }

            if (preg_match('/(?:jersey|জার্সি|জার্সী)/iu', $candidate)) {
                array_push($queries, 'jersey', 'জার্সি', 'football jersey', 'football shirt');
                break;
            }
        }

        return array_values(array_unique(array_map(fn (string $value) => trim($value), $queries)));
    }

    private function isProductSearchRequest(string $message): bool
    {
        if (preg_match('/\b(?:order|tracking|ORD-[A-Z0-9]+)\b/i', $message)) {
            return false;
        }

        return preg_match('/(?:খুঁজ|খোঁজ|দেখাও|দেখান|দেখতে চাই|দাও|দিন|দে|লাগবে|চাই|show me|find|search|looking for|\b(?:dao|den|lagbe|chai)\b|khuj|khuje|khujte|dekhao|dekhaw|show koro)/iu', $message) === 1;
    }

    private function isWhatsappContactRequest(string $message): bool
    {
        $mentionsWhatsApp = preg_match('/whats\s*app|হোয়াটসঅ্যাপ|হোয়াটসঅ্যাপ/iu', $message) === 1;
        $asksForContact = preg_match('/number|\bno\b|contact|message|chat|knock|যোগাযোগ|নম্বর|নক|বার্তা|দাও|দিন|দিতে|কোথায়|কোথায়|কীভাবে/iu', $message) === 1;

        return $mentionsWhatsApp && $asksForContact;
    }

    /** @return array{message: string, whatsapp_url: string, phone: string} */
    private function whatsappSupport(string $message): array
    {
        return [
            'message' => $message.' নম্বর: 01856533432',
            'whatsapp_url' => 'https://wa.me/8801856533432?text='.rawurlencode('Hello, I need help from OneHaat.'),
            'phone' => '01856533432',
        ];
    }
}
