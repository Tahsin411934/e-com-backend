<?php

namespace Modules\Store\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Store\Models\Store;
use Modules\Store\Http\Requests\StoreOwnerRegistrationRequest;
use Modules\Store\Services\StoreRegistrationService;

class StoreAuthController extends Controller
{
    public function __construct(private StoreRegistrationService $registrationService) {}

    /**
     * Register a new store owner (SaaS tenant) together with their store.
     */
    public function register(StoreOwnerRegistrationRequest $request)
    {
        return $this->registrationService->registerStoreOwner($request->validated());
    }

    public function slugAvailability(Request $request)
    {
        $slug = Str::slug((string) $request->query('slug'));

        if ($slug === '') {
            return ApiResponse::success(['available' => false, 'slug' => $slug, 'reason' => 'invalid']);
        }

        $available = ! Store::withTrashed()->where('slug', $slug)->exists();

        return ApiResponse::success([
            'available' => $available,
            'slug' => $slug,
            'reason' => $available ? null : 'taken',
        ]);
    }

    /**
     * Get the store owned by the authenticated store owner.
     */
    public function myStore(Request $request)
    {
        $store = $request->user()->ownedStore;

        if (! $store) {
            return ApiResponse::notFound('No store is associated with your account.');
        }

        return ApiResponse::success(['store' => $store]);
    }
}
