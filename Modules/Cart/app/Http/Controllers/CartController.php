<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Cart\Services\CartService;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

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
        $result = $this->cartService->saveCart($request->all());
        return response()->json($result);
    }

    public function show(int $id)
    {
        $result = $this->cartService->getCartById($id);
        return response()->json($result);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->all();
        $data['cart_id'] = $id;
        $result = $this->cartService->saveCart($data);
        return response()->json($result);
    }

    public function destroy(int $id)
    {
        $result = $this->cartService->deleteCart($id);
        return response()->json($result);
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

        $result = $this->cartService->addToCart($data);
        return response()->json($result);
    }

    public function updateItem(Request $request, int $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $result = $this->cartService->updateCartItem($itemId, $request->only('quantity'));
        return response()->json($result);
    }

    public function removeItem(int $itemId)
    {
        $result = $this->cartService->removeCartItem($itemId);
        return response()->json($result);
    }

    public function myCart()
    {
        $userId = auth()->id();

        // Prices are recalculated server-side (campaign > 0 wins, else
        // variant/option discount) so the cart never returns stale prices.
        return response()->json([
            'status' => 'success',
            'cart' => $this->cartService->freshPricedCart($userId),
        ]);
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

        $result = $this->cartService->syncCart($data);
        return response()->json($result);
    }
}
