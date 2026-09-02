<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Cart\Services\CampaignService;

class CampaignApiController extends Controller
{
    public function __construct(private CampaignService $campaignService) {}

    public function index()
    {
        return $this->campaignService->liveCampaigns();
    }

    public function show(string $slug)
    {
        return $this->campaignService->liveCampaignBySlug($slug);
    }
}
