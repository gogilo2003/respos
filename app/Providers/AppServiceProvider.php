<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Interfaces\Repositories\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );
        $this->app->bind(
            \App\Interfaces\Repositories\RoleRepositoryInterface::class,
            \App\Repositories\RoleRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        $roles = ['admin', 'manager', 'cashier', 'kitchen', 'waiter', 'customer'];

        foreach ($roles as $role) {
            \Illuminate\Support\Facades\Gate::define($role, function (\App\Models\User $user) use ($role) {
                return $user->hasRole($role);
            });
        }
    }
}
