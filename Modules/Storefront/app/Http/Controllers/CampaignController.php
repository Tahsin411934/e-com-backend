<?php

namespace Modules\Storefront\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Cart\Services\CampaignService;

/**
 * Storefront campaign API.
 *
 * Same response shape as the legacy /api/v1/campaigns endpoints, scoped to
 * the resolved tenant: this store's campaigns + global platform campaigns,
 * and only products visible on this storefront.
 */
class CampaignController extends Controller
{
    public function __construct(private readonly CampaignService $campaignService) {}

    public function index(): JsonResponse
    {
        return $this->campaignService->liveCampaignsForStorefront();
    }

    public function show(string $slug): JsonResponse
    {
        return $this->campaignService->liveCampaignBySlugForStorefront($slug);
    }
}