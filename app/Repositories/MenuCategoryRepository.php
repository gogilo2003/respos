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
}
