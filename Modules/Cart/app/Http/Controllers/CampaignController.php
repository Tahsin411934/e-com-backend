<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Cart\Models\Campaign;
use Modules\Cart\Services\CampaignService;

class CampaignController extends Controller
{
    public function __construct(private CampaignService $campaignService) {}

    public function index()
    {
        return view('cart::campaigns.index');
    }

    public function list()
    {
        return $this->campaignService->list();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'button_text' => 'nullable|string|max:60',
            'priority' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'status' => 'required|in:draft,active,paused',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        if ($request->hasFile('banner_image')) {
            $data['banner_image_file'] = $request->file('banner_image');
        }
        unset($data['banner_image']);

        return $this->campaignService->store($data);
    }

    public function show(Campaign $campaign)
    {
        return $this->campaignService->show($campaign);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:160',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'button_text' => 'nullable|string|max:60',
            'priority' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'sometimes|nullable|boolean',
            'status' => 'sometimes|required|in:draft,active,paused',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        if ($request->hasFile('banner_image')) {
            $data['banner_image_file'] = $request->file('banner_image');
        }
        unset($data['banner_image']);

        return $this->campaignService->update($campaign, $data, $request->boolean('remove_banner'));
    }

    public function destroy(Campaign $campaign)
    {
        return $this->campaignService->destroy($campaign);
    }

    public function toggleActive(Campaign $campaign)
    {
        return $this->campaignService->toggleActive($campaign);
    }

    public function searchProducts(Request $request)
    {
        return $this->campaignService->searchProducts((string) $request->string('q')->trim());
    }

    public function addProduct(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'discount_type' => 'required|in:percentage,fixed_amount,fixed_price',
            'discount_value' => 'required|numeric|min:0',
        ]);

        return $this->campaignService->addProduct($campaign, $data);
    }

    public function updateProduct(Request $request, Campaign $campaign, int $campaignProduct)
    {
        $data = $request->validate([
            'discount_type' => 'sometimes|required|in:percentage,fixed_amount,fixed_price',
            'discount_value' => 'sometimes|required|numeric|min:0',
        ]);

        return $this->campaignService->updateProduct($campaign, $campaignProduct, $data);
    }

    public function reorderProducts(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'integer',
        ]);

        return $this->campaignService->reorderProducts($campaign, $data['ordered_ids']);
    }

    public function removeProduct(Campaign $campaign, int $campaignProduct)
    {
        return $this->campaignService->removeProduct($campaign, $campaignProduct);
    }
}
