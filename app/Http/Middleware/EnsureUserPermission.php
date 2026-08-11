<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $targetPermission = $permission ?? $request->route()?->getName();

        if (! $targetPermission) {
            return $next($request);
        }

        // Handle pipe separated permissions if any e.g. "users.index|users.manage"
        $permissionList = explode('|', $targetPermission);
        foreach ($permissionList as $perm) {
            if ($user->hasPermission(trim($perm))) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized action for assigned permissions.');
    }
}
