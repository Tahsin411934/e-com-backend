<?php

namespace Modules\Identity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Identity\Models\Customer;
use Modules\Store\Support\CurrentStore;

class EnsureCentralCustomer
{
    public function handle(Request $request, Closure $next)
    {
        $customer = $request->user();
        abort_unless($customer instanceof Customer && $customer->store_id === null, 403, 'A central customer account is required.');
        abort_unless($customer->status === 'active' && ! $customer->trashed(), 403, 'This customer account is not active.');
        CurrentStore::set(null);

        return $next($request);
    }
}
