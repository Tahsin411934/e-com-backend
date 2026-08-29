<?php

namespace Modules\Reviews\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Reviews\Models\Webhook;
use Modules\Reviews\Services\WebhookDeliveryService;

class WebhookDeliveryController extends Controller
{
    public function __construct(private WebhookDeliveryService $service) {}

    public function index()
    {
        $webhooks = Webhook::orderBy('name')->get(['id', 'name']);

        return view('reviews::webhook-deliveries.index', compact('webhooks'));
    }

    public function dataTable(Request $request)
    {
        return $this->service->getDeliveryDataTable($request);
    }

    public function show($id)
    {
        return $this->service->getDeliveryById((int) $id);
    }

    public function destroy($id)
    {
        return $this->service->deleteDelivery((int) $id);
    }
}
