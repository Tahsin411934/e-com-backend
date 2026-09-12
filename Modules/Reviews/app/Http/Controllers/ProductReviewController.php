<?php

namespace Modules\Reviews\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Models\Product;
use Modules\Reviews\Http\Requests\ProductReviewRequest;
use Modules\Reviews\Services\ProductReviewService;

class ProductReviewController extends Controller
{
    public function __construct(private ProductReviewService $service) {}

    public function index()
    {
        $products = Product::orderBy('name')->get(['id', 'name']);

        return view('reviews::product-reviews.index', compact('products'));
    }

    public function dataTable(Request $request)
    {
        return $this->service->getReviewDataTable($request);
    }

    public function store(ProductReviewRequest $request)
    {
        return $this->service->saveReview($request->validated());
    }

    public function show($id)
    {
        return $this->service->getReviewById((int) $id);
    }

    public function update(ProductReviewRequest $request, $id)
    {
        return $this->service->saveReview($request->validated() + ['review_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->service->deleteReview((int) $id);
    }

    public function approve($id)
    {
        return $this->service->approveReview((int) $id);
    }
}
