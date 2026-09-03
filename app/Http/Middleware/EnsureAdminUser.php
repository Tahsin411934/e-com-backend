<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminUser
{
    /**
     * Ensure the authenticated user is an admin-panel user.
     *
     * Customers register through the public API into the same users
     * table, so authentication alone does not protect the admin panel.
     * A user may access the panel only when they hold at least one
     * staff-side role (Super Admin, Admin, Manager, Staff, ...).
     * Frontend customers carry only the "Customer" role (or no role
     * at all) and are rejected here.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasAdminAccess()) {
            abort(403, 'You do not have permission to access the admin panel.');
        }

        return $next($request);
    }
}
