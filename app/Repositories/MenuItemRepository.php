<?php

namespace App\Repositories;

use App\Interfaces\Repositories\MenuItemRepositoryInterface;
use App\Models\MenuItem;

class MenuItemRepository extends BaseRepository implements MenuItemRepositoryInterface
{
    public function __construct(MenuItem $model)
    {
        parent::__construct($model);
    }

    public function getAvailableItems()
    {
        return $this->model->where('is_available', true)
            ->with('category')
            ->orderBy('sort_order')
            ->get();
    }

    public function getItemsByCategory($categoryId)
    {
        return $this->model->where('category_id', $categoryId)
            ->orderBy('sort_order')
            ->get();
    }

    public function getItemsWithCategory()
    {
        return $this->model->with('category')
            ->orderBy('sort_order')
            ->get();
    }
}
