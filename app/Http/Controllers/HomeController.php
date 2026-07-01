<?php

namespace App\Http\Controllers;

use App\Services\MenuService;
use Inertia\Inertia;

class HomeController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $MenuService)
    {
        $this->menuService = $MenuService;
    }

    public function welcome()
    {
        $menuItems = $this->menuService->getMenuItems();

        return Inertia::render('Welcome', ['menuItems' => $menuItems]);
    }

    public function categories()
    {
        return Inertia::render('Categories', ['categories' => $this->menuService->getMenuCategories()]);
    }

    public function menu()
    {
        $menuItems = $this->menuService->getMenuItems();

        return Inertia::render('Menu', ['menuItems' => $menuItems]);
    }

    public function about()
    {
        return Inertia::render('About');
    }

    public function contact()
    {
        return Inertia::render('Contact');
    }
}
