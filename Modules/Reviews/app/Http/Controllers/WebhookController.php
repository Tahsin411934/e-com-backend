<?php

namespace Modules\Reviews\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Reviews\Http\Requests\WebhookRequest;
use Modules\Reviews\Services\WebhookService;

class WebhookController extends Controller
{
    public function __construct(private WebhookService $service) {}

    public function index()
    {
        return view('reviews::webhooks.index');
    }

    public function dataTable(Request $request)
    {
        return $this->service->getWebhookDataTable($request);
    }

    public function store(WebhookRequest $request)
    {
        return $this->service->saveWebhook($request->validated());
    }

    public function show($id)
    {
        return $this->service->getWebhookById((int) $id);
    }

    public function update(WebhookRequest $request, $id)
    {
        return $this->service->saveWebhook($request->validated() + ['webhook_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->service->deleteWebhook((int) $id);
    }
}
