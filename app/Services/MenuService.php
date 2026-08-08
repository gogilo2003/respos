<?php

namespace App\Services;

use App\Http\Resources\MenuCategoryResource;
use App\Http\Resources\MenuItemResource;
use App\Interfaces\Repositories\MenuCategoryRepositoryInterface;
use App\Interfaces\Repositories\MenuItemRepositoryInterface;
use App\Models\User;

class MenuService
{
    public function __construct(
        protected MenuCategoryRepositoryInterface $menuCategoryRepository,
        protected MenuItemRepositoryInterface $menuItemRepository
    ) {}

    /**
     * Resolve role string from User model or fallback to guest/customer.
     */
    public function resolveUserRole(?User $user): string
    {
        if (! $user) {
            return 'guest';
        }

        return $user->role?->name ?? 'customer';
    }

    /**
     * Get centralized menu (categories with nested items) filtered by role.
     */
    public function getCentralizedMenu(?User $user = null): array
    {
        $role = $this->resolveUserRole($user);
        $categories = $this->menuCategoryRepository->getCategoriesForRole($role);
        $allItems = $this->menuItemRepository->getItemsForRole($role);

        $groupedItems = $allItems->groupBy('category_id');

        return $categories->map(function ($category) use ($groupedItems) {
            $items = $groupedItems->get($category->id, collect());
            $category->setRelation('menuItems', $items);

            return (new MenuCategoryResource($category))->resolve();
        })->toArray();
    }

    /**
     * Get menu items filtered by role.
     */
    public function getMenuItems(?User $user = null, ?int $categoryId = null): array
    {
        $role = $this->resolveUserRole($user);
        $items = $this->menuItemRepository->getItemsForRole($role, $categoryId);

        return MenuItemResource::collection($items)->resolve();
    }

    /**
     * Get menu categories filtered by role.
     */
    public function getMenuCategories(?User $user = null): array
    {
        $role = $this->resolveUserRole($user);
        $categories = $this->menuCategoryRepository->getCategoriesForRole($role);

        return MenuCategoryResource::collection($categories)->resolve();
    }
}
