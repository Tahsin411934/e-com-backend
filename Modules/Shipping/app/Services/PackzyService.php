<?php

namespace Modules\Shipping\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Order\Models\Order;

class PackzyService
{
    public function book(Order $order): ?array
    {
        $config = config('services.packzy');
        if (! ($config['enabled'] ?? false) || empty($config['api_key']) || empty($config['secret_key'])) {
            return null;
        }

        $delivery = $order->deliveries()->latest()->first();
        if (! $delivery) {
            return null;
        }

        try {
            $response = $this->client($config)->post('/create_order', [
                'invoice' => $order->order_number,
                'recipient_name' => $order->customer_name ?: ($order->user?->name ?? 'Customer'),
                'recipient_phone' => $delivery->delivery_phone,
                'recipient_address' => trim($delivery->delivery_address.' '.$delivery->delivery_city),
                'cod_amount' => (float) $order->grand_total,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Packzy order booking request failed.', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        if ($response->successful()) {
            return $response->json();
        }

        Log::warning('Packzy order booking failed.', [
            'order_id' => $order->id,
            'status' => $response->status(),
            'body' => $response->json() ?: $response->body(),
        ]);

        return null;
    }

    private function client(array $config): PendingRequest
    {
        return Http::baseUrl(rtrim($config['url'], '/'))
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->withHeaders([
                'Api-Key' => $config['api_key'],
                'Secret-Key' => $config['secret_key'],
            ]);
    }
}
