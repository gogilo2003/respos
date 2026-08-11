<?php

namespace App\Services;

use App\Models\User;

class PermissionRegistry
{
    /**
     * Single Source of Truth: Catalog of all system permissions,
     * route mappings, labels, default roles, and sidebar nav configurations.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllPermissions(): array
    {
        return [
            // User Management
            [
                'key' => 'users.index',
                'group' => 'Users & Access',
                'label' => 'View Staff Users',
                'routeName' => 'users',
                'defaultRoles' => ['admin'],
                'nav' => [
                    'showInNav' => true,
                    'label' => 'Users',
                    'shortLabel' => 'U',
                    'activePattern' => 'users*',
                    'order' => 20,
                ],
            ],
            [
                'key' => 'users.manage',
                'group' => 'Users & Access',
                'label' => 'Create, Edit & Suspend Users',
                'routeName' => 'users.store',
                'defaultRoles' => ['admin'],
                'nav' => ['showInNav' => false],
            ],

            // Role Management
            [
                'key' => 'roles.index',
                'group' => 'Users & Access',
                'label' => 'Manage Roles & Permissions',
                'routeName' => 'roles.index',
                'defaultRoles' => ['admin'],
                'nav' => [
                    'showInNav' => true,
                    'label' => 'Role Permissions',
                    'shortLabel' => 'P',
                    'activePattern' => 'roles*',
                    'order' => 25,
                ],
            ],

            // Menu Categories
            [
                'key' => 'menu-categories.index',
                'group' => 'Menu Management',
                'label' => 'View & Manage Categories',
                'routeName' => 'menu-categories',
                'defaultRoles' => ['admin', 'manager'],
                'nav' => [
                    'showInNav' => true,
                    'label' => 'Menu Categories',
                    'shortLabel' => 'C',
                    'activePattern' => 'menu-categories*',
                    'order' => 30,
                ],
            ],

            // Menu Items
            [
                'key' => 'menu-items.index',
                'group' => 'Menu Management',
                'label' => 'View & Manage Menu Items',
                'routeName' => 'menu-items',
                'defaultRoles' => ['admin', 'manager'],
                'nav' => [
                    'showInNav' => true,
                    'label' => 'Menu Items',
                    'shortLabel' => 'M',
                    'activePattern' => 'menu-items*',
                    'order' => 35,
                ],
            ],
            [
                'key' => 'menu-items.availability',
                'group' => 'Menu Management',
                'label' => 'Toggle Daily Item Availability',
                'routeName' => 'menu-items.toggle-availability',
                'defaultRoles' => ['admin', 'manager', 'kitchen'],
                'nav' => ['showInNav' => false],
            ],

            // Tables & QR Codes
            [
                'key' => 'tables.index',
                'group' => 'Table Management',
                'label' => 'Manage Restaurant Tables & QR Codes',
                'routeName' => 'tables',
                'defaultRoles' => ['admin', 'manager'],
                'nav' => [
                    'showInNav' => true,
                    'label' => 'Tables',
                    'shortLabel' => 'T',
                    'activePattern' => 'tables*',
                    'order' => 40,
                ],
            ],

            // Billing & Invoicing
            [
                'key' => 'bills.index',
                'group' => 'Billing & Cashier',
                'label' => 'View Active Bills & Process Payments',
                'routeName' => 'bills.index',
                'defaultRoles' => ['admin', 'manager', 'cashier'],
                'nav' => [
                    'showInNav' => true,
                    'label' => 'Bills',
                    'shortLabel' => 'B',
                    'activePattern' => 'bills*',
                    'order' => 50,
                ],
            ],
            [
                'key' => 'bills.void',
                'group' => 'Billing & Cashier',
                'label' => 'Void Bills (Manager Override)',
                'routeName' => 'bills.void',
                'defaultRoles' => ['admin', 'manager'],
                'nav' => ['showInNav' => false],
            ],

            // Kitchen Operations
            [
                'key' => 'kitchen.dashboard',
                'group' => 'Kitchen Operations',
                'label' => 'Kitchen Display System',
                'routeName' => 'kitchen.dashboard',
                'defaultRoles' => ['admin', 'manager', 'kitchen'],
                'nav' => [
                    'showInNav' => true,
                    'label' => 'Kitchen Display',
                    'shortLabel' => 'K',
                    'activePattern' => 'kitchen*',
                    'order' => 60,
                ],
            ],

            // Waiter Operations
            [
                'key' => 'waiter.dashboard',
                'group' => 'Waiter Operations',
                'label' => 'Waiter Workspace & Ordering',
                'routeName' => 'waiter.dashboard',
                'defaultRoles' => ['admin', 'manager', 'waiter'],
                'nav' => [
                    'showInNav' => true,
                    'label' => 'Waiter Station',
                    'shortLabel' => 'W',
                    'activePattern' => 'waiter*',
                    'order' => 70,
                ],
            ],

            // Cash Reconciliation
            [
                'key' => 'reconciliations.index',
                'group' => 'Reports & Auditing',
                'label' => 'Submit Daily Cash Balance Count',
                'routeName' => 'reconciliations.index',
                'defaultRoles' => ['admin', 'manager', 'cashier'],
                'nav' => [
                    'showInNav' => true,
                    'label' => 'Reconciliation',
                    'shortLabel' => 'R',
                    'activePattern' => 'reconciliations*',
                    'order' => 80,
                ],
            ],
            [
                'key' => 'reconciliations.approve',
                'group' => 'Reports & Auditing',
                'label' => 'Approve Flagged Cash Reconciliations',
                'routeName' => 'reconciliations.approve',
                'defaultRoles' => ['admin', 'manager'],
                'nav' => ['showInNav' => false],
            ],

            // Audit Logs
            [
                'key' => 'audit-logs.index',
                'group' => 'Reports & Auditing',
                'label' => 'View System Audit Logs',
                'routeName' => 'audit-logs.index',
                'defaultRoles' => ['admin'],
                'nav' => [
                    'showInNav' => true,
                    'label' => 'Audit Logs',
                    'shortLabel' => 'A',
                    'activePattern' => 'audit-logs*',
                    'order' => 90,
                ],
            ],
        ];
    }

    /**
     * Get default permission keys array for a given role name.
     *
     * @return array<int, string>
     */
    public function getDefaultPermissionsForRole(string $role): array
    {
        if ($role === 'admin') {
            return array_column($this->getAllPermissions(), 'key');
        }

        $matchingKeys = [];
        foreach ($this->getAllPermissions() as $item) {
            if (in_array($role, $item['defaultRoles'], true)) {
                $matchingKeys[] = $item['key'];
            }
        }

        return $matchingKeys;
    }

    /**
     * Get sidebar navigation items dynamically filtered by the user's permissions.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getNavigationItemsForUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $navItems = [
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'routeName' => 'dashboard',
                'url' => $this->resolveUrl('dashboard'),
                'activePattern' => 'dashboard*',
                'shortLabel' => 'D',
                'order' => 10,
            ],
        ];

        foreach ($this->getAllPermissions() as $permission) {
            if (empty($permission['nav']['showInNav'])) {
                continue;
            }

            if (! $user->hasPermission($permission['key'])) {
                continue;
            }

            $navItems[] = [
                'key' => $permission['key'],
                'label' => $permission['nav']['label'],
                'routeName' => $permission['routeName'],
                'url' => $this->resolveUrl($permission['routeName']),
                'activePattern' => $permission['nav']['activePattern'],
                'shortLabel' => $permission['nav']['shortLabel'],
                'order' => $permission['nav']['order'] ?? 50,
            ];
        }

        usort($navItems, fn ($a, $b) => ($a['order'] ?? 50) <=> ($b['order'] ?? 50));

        return $navItems;
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
