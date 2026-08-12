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

    public function getItemsForRole(string $role, ?int $categoryId = null)
    {
        $query = $this->model->with('category');

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        if (in_array($role, ['customer', 'guest'], true)) {
            $query->where('is_available', true)
                ->whereHas('category', function ($q) {
                    $q->where('is_active', true);
                });
        } elseif (in_array($role, ['waiter', 'cashier', 'kitchen'], true)) {
            $query->whereHas('category', function ($q) {
                $q->where('is_active', true);
            });
        }

        return $query->orderBy('sort_order')->get();
    }

    public function getFeaturedItems()
    {
        return $this->model->where('is_featured', true)
            ->where('is_available', true)
            ->with('category')
            ->orderBy('sort_order')
            ->get();
    }
}
