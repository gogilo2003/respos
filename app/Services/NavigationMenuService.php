<?php

namespace App\Services;

use App\Models\User;

class NavigationMenuService
{
    protected PermissionRegistry $permissionRegistry;

    public function __construct(?PermissionRegistry $permissionRegistry = null)
    {
        $this->permissionRegistry = $permissionRegistry ?? app(PermissionRegistry::class);
    }

    /**
     * Get role/permission filtered navigation menu items for the authenticated user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getNavigationMenu(?User $user): array
    {
        return $this->permissionRegistry->getNavigationItemsForUser($user);
    }
}
