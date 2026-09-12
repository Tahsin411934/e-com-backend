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
     * @param  string  ...$permissions  One or more permission names (supports "x.*" wildcards)
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

        // Resource wildcards resolve to the permission required by the current
        // controller action. A user with only `products.view` must never pass
        // an update or destroy route protected by `products.*`.
        $hasPermission = collect($permissions)->contains(function ($permission) use ($request, $userPermissions) {
            if (str_ends_with($permission, '.*')) {
                $ability = $this->abilityForAction($request->route()?->getActionMethod());

                return $ability !== null
                    && $userPermissions->contains(substr($permission, 0, -1).$ability);
            }

            return $userPermissions->contains($permission);
        });

        if (! $hasPermission) {
            abort(403, 'Unauthorized. You do not have the required permission to access this resource.');
        }

        return $next($request);
    }

    private function abilityForAction(?string $action): ?string
    {
        return match ($action) {
            'index', 'show', 'dataTable' => 'view',
            'create', 'store' => 'create',
            'edit', 'update' => 'edit',
            'destroy' => 'delete',
            default => null,
        };
    }
}
