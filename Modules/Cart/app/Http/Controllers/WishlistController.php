<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Cart\Http\Requests\WishlistRequest;
use Modules\Cart\Services\WishlistService;

class WishlistController extends Controller
{
    public function __construct(private WishlistService $wishlistService) {}

    public function index()
    {
        return view('cart::wishlists.index');
    }

    public function dataTable(Request $request)
    {
        return $this->wishlistService->getWishlistDataTable($request);
    }

    public function store(WishlistRequest $request)
    {
        return $this->wishlistService->saveWishlist($request->validated());
    }

    public function show($id)
    {
        return $this->wishlistService->getWishlistById((int) $id);
    }

    public function update(WishlistRequest $request, int $id)
    {
        $data = $request->validated();
        $data['wishlist_id'] = $id;

        return $this->wishlistService->saveWishlist($data);
    }

    public function destroy($id)
    {
        return $this->wishlistService->deleteWishlist((int) $id);
    }

    public function apiIndex()
    {
        return $this->wishlistService->getCurrentUserWishlist();
    }

    public function apiToggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        return $this->wishlistService->toggleWishlist((int) $request->input('product_id'));
    }

    public function apiRemove(int $productId)
    {
        $userId = auth()->id();

        return $this->wishlistService->removeWishlistItem($userId, $productId);
    }
}
