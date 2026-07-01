<?php

namespace App\Interfaces\Repositories;

interface MenuCategoryRepositoryInterface extends RepositoryInterface
{
    public function getActiveCategories();

    public function getCategoriesWithItemCount();
}
