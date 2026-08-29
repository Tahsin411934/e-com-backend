<?php

namespace Modules\Identity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$permissions  One or more permission names
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Super Admin has all permissions
        if ($user->roles->contains('name', 'Super Admin')) {
            return $next($request);
        }

        // Get all permission names from user's roles
        $userPermissions = $user->roles
            ->flatMap->permissions
            ->pluck('name')
            ->unique();

        // Check if user has any of the specified permissions
        $hasPermission = collect($permissions)->contains(function ($permission) use ($userPermissions) {
            return $userPermissions->contains($permission);
        });

        if (! $hasPermission) {
            abort(403, 'Unauthorized. You do not have the required permission to access this resource.');
        }

        return $next($request);
    }
}
