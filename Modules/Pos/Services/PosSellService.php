<?php

namespace Modules\Pos\Services;

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
    public function searchCustomers(Request $request): array
    {
        try {
            $term = $request->get('term', '');
            
            $query = User::query()
                ->select('id', 'first_name', 'last_name', 'phone', 'email')
                ->where('status', 'active');
            
            if (!empty($term)) {
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

            return [
                'status' => 'success',
                'customers' => $customers,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error searching customers: ' . $e->getMessage(),
                'customers' => [],
            ];
        }
    }

    /**
     * Search products by name, SKU or barcode
     */
    public function searchProducts(Request $request): array
    {
        try {
            $term = $request->get('term', '');
            
            $query = Product::query()
                ->with(['variants', 'brand', 'unit'])
                ->where('status', 'active');
            
            if (!empty($term)) {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'LIKE', "%{$term}%")
                      ->orWhereHas('variants', function ($vq) use ($term) {
                          $vq->where('sku', 'LIKE', "%{$term}%");
                      });
                });
            }

            $products = $query->orderBy('name')
                ->limit(20)
                ->get()
                ->map(function ($product) {
                    $variant = $product->variants->first();
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'product_type' => $product->product_type,
                        'brand' => $product->brand ? $product->brand->name : '',
                        'unit' => $product->unit ? $product->unit->name : '',
                        'sku' => $variant ? $variant->sku : '',
                        'price' => $variant ? ($variant->sale_price ?? $variant->price ?? 0) : 0,
                        'stock' => 0,
                        'image' => $product->images->first()?->image_url ?? '',
                    ];
                });

            return [
                'status' => 'success',
                'products' => $products,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error searching products: ' . $e->getMessage(),
                'products' => [],
            ];
        }
    }

    /**
     * Process the POS sale and save it
     */
    public function processSale(Request $request): array
    {
        try {
            return DB::transaction(function () use ($request) {
                $data = $request->validate([
                    'customer_id' => 'nullable|exists:users,id',
                    'register_id' => 'required|exists:pos_registers,id',
                    'shift_id' => 'required|exists:pos_shifts,id',
                    'items' => 'required|array|min:1',
                    'items.*.product_id' => 'required|exists:products,id',
                    'items.*.product_name' => 'required|string|max:220',
                    'items.*.sku' => 'nullable|string|max:100',
                    'items.*.unit_price' => 'required|numeric|min:0',
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

                $receiptNumber = 'POS-' . strtoupper(uniqid());

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
                        'variant_id' => null,
                        'product_name' => $item['product_name'],
                        'sku' => $item['sku'] ?? null,
                        'unit_price' => $item['unit_price'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['subtotal'],
                        'tax_amount' => 0,
                        'discount_amount' => 0,
                        'total' => $item['total'],
                    ]);
                }

                if (Schema::hasTable('account_transactions')) {
                    app(AccountTransactionService::class)->postPosSale($sale->fresh(['items.product.variants', 'register.store']));
                }

                return [
                    'status' => 'success',
                    'message' => 'Sale completed successfully!',
                    'sale' => $sale->fresh()->load(['items', 'register', 'shift']),
                    'receipt' => [
                        'receipt_number' => $receiptNumber,
                        'total' => $data['total'],
                        'items_count' => count($data['items']),
                    ],
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error processing sale: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get last few sales for the current register/shift
     */
    public function getRecentSales(Request $request): array
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

            return [
                'status' => 'success',
                'sales' => $sales,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error fetching recent sales: ' . $e->getMessage(),
                'sales' => [],
            ];
        }
    }
}
