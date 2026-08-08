<?php

namespace App\Providers;

use App\Interfaces\Repositories\AssistanceRequestRepositoryInterface;
use App\Interfaces\Repositories\CashReconciliationRepositoryInterface;
use App\Interfaces\Repositories\KitchenRepositoryInterface;
use App\Interfaces\Repositories\MenuCategoryRepositoryInterface;
use App\Interfaces\Repositories\MenuItemRepositoryInterface;
use App\Interfaces\Repositories\OrderRepositoryInterface;
use App\Interfaces\Repositories\RoleRepositoryInterface;
use App\Interfaces\Repositories\TableRepositoryInterface;
use App\Interfaces\Repositories\TableSessionRepositoryInterface;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Interfaces\Repositories\WaiterStatisticsRepositoryInterface;
use App\Models\Bill;
use App\Models\User;
use App\Policies\BillPolicy;
use App\Repositories\AssistanceRequestRepository;
use App\Repositories\CashReconciliationRepository;
use App\Repositories\KitchenRepository;
use App\Repositories\MenuCategoryRepository;
use App\Repositories\MenuItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\RoleRepository;
use App\Repositories\TableRepository;
use App\Repositories\TableSessionRepository;
use App\Repositories\UserRepository;
use App\Repositories\WaiterStatisticsRepository;
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
        $this->app->bind(
            TableRepositoryInterface::class,
            TableRepository::class
        );
        $this->app->bind(
            TableSessionRepositoryInterface::class,
            TableSessionRepository::class
        );
        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );
        $this->app->bind(
            KitchenRepositoryInterface::class,
            KitchenRepository::class
        );
        $this->app->bind(
            AssistanceRequestRepositoryInterface::class,
            AssistanceRequestRepository::class
        );
        $this->app->bind(
            WaiterStatisticsRepositoryInterface::class,
            WaiterStatisticsRepository::class
        );
        $this->app->bind(
            CashReconciliationRepositoryInterface::class,
            CashReconciliationRepository::class
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

        Gate::policy(Bill::class, BillPolicy::class);
    }
}
