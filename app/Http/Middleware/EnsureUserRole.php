<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        foreach ($roles as $roleGroup) {
            $roleList = explode('|', $roleGroup);
            foreach ($roleList as $role) {
                if ($user->hasRole(trim($role))) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Unauthorized action for role.');
    }
}
