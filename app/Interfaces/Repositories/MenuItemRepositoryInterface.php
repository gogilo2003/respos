<?php

namespace App\Interfaces\Repositories;

interface MenuItemRepositoryInterface extends RepositoryInterface
{
    public function getAvailableItems();

    public function getItemsByCategory($categoryId);

    public function getItemsWithCategory();
}
