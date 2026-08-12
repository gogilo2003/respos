<?php

namespace App\Http\Middleware;

use App\Services\NavigationMenuService;
use App\Services\PermissionRegistry;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(protected NavigationMenuService $navigationMenuService) {}

    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'username' => $request->user()->username,
                    'role' => $request->user()->role?->name,
                    'permissions' => $request->user()->hasRole('admin')
                        ? array_column(app(PermissionRegistry::class)->getAllPermissions(), 'key')
                        : ($request->user()->role?->permissions ?? app(PermissionRegistry::class)->getDefaultPermissionsForRole($request->user()->role?->name ?? '')),
                ] : null,
            ],
            'navigationMenu' => $this->navigationMenuService->getNavigationMenu($request->user()),
            'currency' => [
                'code' => config('billing.currency', 'KES'),
                'symbol' => config('billing.currency_symbol', 'KES '),
            ],
            'activeOrderId' => session('active_order_id') ?: ($request->route('order')?->id ?? null),
        ];
    }
}
