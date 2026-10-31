<?php

namespace Modules\Storefront\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Identity\Models\Customer;
use Modules\Store\Support\CurrentStore;

class EnsureStorefrontCustomer
{
    public function handle(Request $request, Closure $next)
    {
        $customer = $request->user();
        abort_unless($customer instanceof Customer, 403, 'A storefront customer account is required.');
        abort_unless(CurrentStore::id() !== null && (int) $customer->store_id === (int) CurrentStore::id(), 403, 'This account belongs to another store.');
        abort_unless($customer->status === 'active' && ! $customer->trashed(), 403, 'This customer account is not active.');

        return $next($request);
    }
}
