<?php

namespace Modules\Store\Services;

use Illuminate\Validation\ValidationException;
use Modules\Store\Models\Plan;
use Modules\Store\Models\Store;
use Modules\Store\Models\StoreSubscription;

class StoreSubscriptionService
{
    public function assign(Store $store, Plan $plan): StoreSubscription
    {
        $startsAt = now();
        return $store->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => $plan->duration_days ? 'trialing' : 'active',
            'starts_at' => $startsAt,
            'ends_at' => $plan->duration_days ? $startsAt->copy()->addDays($plan->duration_days) : null,
            'metadata' => ['assigned_by' => 'registration'],
        ]);
    }

    public function current(Store $store): ?StoreSubscription
    {
        $subscription = $store->subscriptions()->with('plan')->whereIn('status', ['trialing', 'active'])->latest('id')->first();
        if ($subscription && ! $subscription->isUsable()) {
            $subscription->update(['status' => 'expired']);
            return null;
        }
        return $subscription;
    }

    public function assertCanCreateProduct(Store $store): void
    {
        $subscription = $this->current($store);
        $limit = $subscription?->plan?->product_limit;
        if ($limit !== null && $store->products()->count() >= $limit) {
            throw ValidationException::withMessages(['products' => "Your {$subscription->plan->name} plan allows up to {$limit} products. Please upgrade your plan."]);
        }
    }
}
