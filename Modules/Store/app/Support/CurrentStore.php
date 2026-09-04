<?php

namespace Modules\Store\Support;

use Illuminate\Support\Facades\Auth;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;
use Modules\Store\Models\StoreStaff;

class CurrentStore
{
    private const CACHE_KEY = 'saas.current_store_id';

    /**
     * Store id the current request is scoped to (memoized per request).
     */
    public static function id(): ?int
    {
        if (app()->bound(self::CACHE_KEY)) {
            return app(self::CACHE_KEY);
        }

        $id = self::resolve();
        app()->instance(self::CACHE_KEY, $id);

        return $id;
    }

    public static function store(): ?Store
    {
        $id = self::id();

        return $id !== null ? Store::find($id) : null;
    }

    /**
     * Force a store id for the remainder of the request (testing / admin tooling).
     */
    public static function set(?int $storeId): void
    {
        app()->instance(self::CACHE_KEY, $storeId);
    }

    public static function reset(): void
    {
        if (app()->bound(self::CACHE_KEY)) {
            app()->forgetInstance(self::CACHE_KEY);
        }
    }

    private static function resolve(): ?int
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return null;
        }

        // Store owners are always scoped to the store they own.
        $ownedId = Store::where('owner_id', $user->id)->value('id');
        if ($ownedId !== null) {
            return (int) $ownedId;
        }

        // Store staff are scoped to their assigned store.
        $staffStoreId = StoreStaff::where('user_id', $user->id)->value('store_id');
        if ($staffStoreId !== null) {
            return (int) $staffStoreId;
        }

        // Platform staff may explicitly filter by store (?store_id=).
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin')) {
            $requested = (int) request()->query('store_id', 0);

            if ($requested > 0) {
                return $requested;
            }
        }

        return null;
    }
}
