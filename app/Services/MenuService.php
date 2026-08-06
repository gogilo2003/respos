<?php

namespace App\Services;

use App\Interfaces\Repositories\MenuItemRepositoryInterface;
use App\Interfaces\Repositories\MenuCategoryRepositoryInterface;
use App\Models\MenuItem;

class MenuService
{
    protected MenuCategoryRepositoryInterface $menuCategoryRepository;
    protected MenuItemRepositoryInterface $menuItemRepository;

    public function __construct(MenuCategoryRepositoryInterface $menuCategoryRepository, MenuItemRepositoryInterface $menuItemRepository)
    {
        $this->menuCategoryRepository = $menuCategoryRepository;
        $this->menuItemRepository = $menuItemRepository;
    }

    public function getMenuItems()
    {
        return $this->menuItemRepository->getAvailableItems()->map(function (MenuItem $item) {
            return [
                'id' => $item->id,
                'title' => $item->name,
                'description' => $item->description,
                'price' => (float) $item->base_price,
                'image' => $item->image_url,
            ];
        })->toArray();
    }

    public function getMenuCategories()
    {
        $categories = $this->menuCategoryRepository->getActiveCategories();

        return $categories;
    }
}
