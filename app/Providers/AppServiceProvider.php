<?php

namespace App\Providers;

use App\Interfaces\Repositories\MenuCategoryRepositoryInterface;
use App\Interfaces\Repositories\MenuItemRepositoryInterface;
use App\Interfaces\Repositories\RoleRepositoryInterface;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Repositories\MenuCategoryRepository;
use App\Repositories\MenuItemRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Gate;
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
            UserRepositoryInterface::class,
            UserRepository::class
        );
        $this->app->bind(
            RoleRepositoryInterface::class,
            RoleRepository::class
        );
        $this->app->bind(
            MenuCategoryRepositoryInterface::class,
            MenuCategoryRepository::class
        );
        $this->app->bind(
            MenuItemRepositoryInterface::class,
            MenuItemRepository::class
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
            Gate::define($role, function (User $user) use ($role) {
                return $user->hasRole($role);
            });
        }
    }
}
