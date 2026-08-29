<?php

namespace Modules\Cart\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Cart\Services\CartService;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function index()
    {
        return view('cart::carts.index');
    }

    public function dataTable(Request $request)
    {
        return $this->cartService->getCartDataTable($request);
    }

    public function store(Request $request)
    {
        return $this->cartService->saveCart($request->all());
    }

    public function show($id)
    {
        return $this->cartService->getCartById((int) $id);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->all();
        $data['cart_id'] = $id;

        return $this->cartService->saveCart($data);
    }

    public function destroy($id)
    {
        return $this->cartService->deleteCart((int) $id);
    }

    public function addItem(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|integer|exists:product_variants,id',
            'variant_option_id' => 'nullable|integer|exists:variant_options,id',
            'quantity' => 'nullable|integer|min:1',
            'store_id' => 'nullable|integer|exists:stores,id',
        ]);

        $data = $request->only(['variant_id', 'variant_option_id', 'quantity', 'store_id']);
        $data['user_id'] = auth()->id();

        return $this->cartService->addToCart($data);
    }

    public function updateItem(Request $request, int $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        return $this->cartService->updateCartItem($itemId, $request->only('quantity'));
    }

    public function removeItem(int $itemId)
    {
        return $this->cartService->removeCartItem($itemId);
    }

    public function myCart()
    {
        $userId = auth()->id();

        // Prices are recalculated server-side (campaign > 0 wins, else
        // variant/option discount) so the cart never returns stale prices.
        return ApiResponse::success($this->cartService->freshPricedCart($userId), 'Cart retrieved successfully.');
    }

    public function syncCart(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.variant_option_id' => 'nullable|integer|exists:variant_options,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $data = $request->only(['items']);
        $data['user_id'] = auth()->id();

        return $this->cartService->syncCart($data);
    }
}
