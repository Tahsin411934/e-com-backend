<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Cart\Models\Campaign;
use Modules\Catalog\Models\Product;

class CampaignController extends Controller
{
    public function index() { return view('cart::campaigns.index'); }

    public function list() { return response()->json(['status' => 'success', 'campaigns' => Campaign::withCount('products')->latest()->get()]); }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:160', 'description' => 'nullable|string', 'banner_image' => 'nullable|string|max:255', 'button_text' => 'nullable|string|max:60', 'priority' => 'nullable|integer|min:0', 'is_featured' => 'nullable|boolean', 'status' => 'required|in:draft,active,paused', 'starts_at' => 'nullable|date', 'ends_at' => 'nullable|date|after:starts_at']);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::lower(Str::random(5));
        return response()->json(['status' => 'success', 'campaign' => Campaign::create($data)], 201);
    }

    public function show(Campaign $campaign) { return response()->json(['status' => 'success', 'campaign' => $campaign->load('products.product')]); }

    public function update(Request $request, Campaign $campaign)
    {
        $data = $request->validate(['name' => 'sometimes|required|string|max:160', 'description' => 'nullable|string', 'banner_image' => 'nullable|string|max:255', 'button_text' => 'nullable|string|max:60', 'priority' => 'nullable|integer|min:0', 'is_featured' => 'nullable|boolean', 'status' => 'sometimes|required|in:draft,active,paused', 'starts_at' => 'nullable|date', 'ends_at' => 'nullable|date|after:starts_at']);
        $campaign->update($data);
        return response()->json(['status' => 'success', 'campaign' => $campaign->fresh()]);
    }

    public function destroy(Campaign $campaign) { $campaign->delete(); return response()->json(['status' => 'success']); }

    public function searchProducts(Request $request)
    {
        $q = $request->string('q')->trim();
        return response()->json(['products' => Product::where('status', 'active')->where(fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"))->with('variants:id,product_id,name,sku,sale_price')->limit(20)->get(['id', 'name', 'slug'])]);
    }

    public function addProduct(Request $request, Campaign $campaign)
    {
        $data = $request->validate(['product_id' => 'required|exists:products,id', 'variant_id' => 'nullable|exists:product_variants,id', 'discount_type' => 'required|in:percentage,fixed_amount,fixed_price', 'discount_value' => 'required|numeric|min:0']);
        $entry = $campaign->products()->updateOrCreate(['product_id' => $data['product_id'], 'variant_id' => $data['variant_id'] ?? null], $data);
        return response()->json(['status' => 'success', 'campaign_product' => $entry->load('product', 'variant')]);
    }

    public function removeProduct(Campaign $campaign, int $campaignProduct) { $campaign->products()->whereKey($campaignProduct)->delete(); return response()->json(['status' => 'success']); }
}
