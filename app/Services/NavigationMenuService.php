<?php

namespace App\Services;

use App\Models\User;

class NavigationMenuService
{
    /**
     * Get role-filtered navigation menu items for the authenticated user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getNavigationMenu(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $role = $user->role?->name;
        $allItems = $this->getAllDefinedItems();

        return array_values(array_filter($allItems, function ($item) use ($role) {
            return in_array($role, $item['roles'], true);
        }));
    }

    /**
     * Define all available navigation items with role permissions.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getAllDefinedItems(): array
    {
        return [
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'routeName' => 'dashboard',
                'url' => $this->resolveUrl('dashboard'),
                'activePattern' => 'dashboard*',
                'shortLabel' => 'D',
                'roles' => ['admin', 'manager', 'cashier', 'waiter', 'kitchen'],
            ],
            [
                'key' => 'users',
                'label' => 'Users',
                'routeName' => 'users',
                'url' => $this->resolveUrl('users'),
                'activePattern' => 'users*',
                'shortLabel' => 'U',
                'roles' => ['admin'],
            ],
            [
                'key' => 'menu-categories',
                'label' => 'Menu Categories',
                'routeName' => 'menu-categories',
                'url' => $this->resolveUrl('menu-categories'),
                'activePattern' => 'menu-categories*',
                'shortLabel' => 'C',
                'roles' => ['admin', 'manager'],
            ],
            [
                'key' => 'menu-items',
                'label' => 'Menu Items',
                'routeName' => 'menu-items',
                'url' => $this->resolveUrl('menu-items'),
                'activePattern' => 'menu-items*',
                'shortLabel' => 'M',
                'roles' => ['admin', 'manager'],
            ],
            [
                'key' => 'tables',
                'label' => 'Tables',
                'routeName' => 'tables',
                'url' => $this->resolveUrl('tables'),
                'activePattern' => 'tables*',
                'shortLabel' => 'T',
                'roles' => ['admin', 'manager'],
            ],
            [
                'key' => 'bills',
                'label' => 'Bills',
                'routeName' => 'bills.index',
                'url' => $this->resolveUrl('bills.index'),
                'activePattern' => 'bills*',
                'shortLabel' => 'B',
                'roles' => ['admin', 'manager', 'cashier'],
            ],
            [
                'key' => 'reconciliations',
                'label' => 'Reconciliation',
                'routeName' => 'reconciliations.index',
                'url' => $this->resolveUrl('reconciliations.index'),
                'activePattern' => 'reconciliations*',
                'shortLabel' => 'R',
                'roles' => ['admin', 'manager', 'cashier'],
            ],
            [
                'key' => 'audit-logs',
                'label' => 'Audit Logs',
                'routeName' => 'audit-logs.index',
                'url' => $this->resolveUrl('audit-logs.index'),
                'activePattern' => 'audit-logs*',
                'shortLabel' => 'A',
                'roles' => ['admin'],
            ],
        ];
    }

    protected function resolveUrl(string $routeName): string
    {
        if (function_exists('app') && app()->bound('router')) {
            try {
                return route($routeName);
            } catch (\Throwable $e) {
                return '/'.str_replace('.', '/', $routeName);
            }
        }

        return '/'.str_replace('.', '/', $routeName);
    }
}
