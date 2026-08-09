<?php

namespace App\Repositories;

use App\Interfaces\Repositories\MenuCategoryRepositoryInterface;
use App\Models\MenuCategory;

class MenuCategoryRepository extends BaseRepository implements MenuCategoryRepositoryInterface
{
    public function __construct(MenuCategory $model)
    {
        parent::__construct($model);
    }

    public function getActiveCategories()
    {
        return $this->model->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getCategoriesWithItemCount()
    {
        return $this->model->withCount('menuItems')
            ->orderBy('sort_order')
            ->get();
    }

    public function getCategoriesForRole(string $role)
    {
        $query = $this->model->newQuery();

        if (! in_array($role, ['admin', 'manager'], true)) {
            $query->where('is_active', true);
        }

        return $query->orderBy('sort_order')->get();
    }
}
