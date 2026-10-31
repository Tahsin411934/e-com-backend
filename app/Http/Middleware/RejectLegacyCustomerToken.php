<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Identity\Models\Customer;

/** Customer IDs must never be interpreted as legacy users.id values. */
class RejectLegacyCustomerToken
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->is('api/v1/storefront', 'api/v1/storefront/*', 'api/v1/central', 'api/v1/central/*')) {
            abort_if(Auth::guard('sanctum')->user() instanceof Customer, 403, 'Use the matching customer API.');
        }

        return $next($request);
    }
}
