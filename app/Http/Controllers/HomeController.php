<?php

namespace App\Http\Controllers;

use App\Services\MenuService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    protected $menuService;
    public function __construct(MenuService $MenuService)
    {
        $this->menuService = $MenuService;
    }
    function welcome()
    {
        $menuItems = $this->menuService->getMenuItems();
        return Inertia::render('Welcome', ['menuItems' => $menuItems]);
    }

    public function welcomeCategories()
    {
        return Inertia::render('WelcomeCategories');
    }

    public function welcomeMenu()
    {
        $menuItems = $this->menuService->getMenuItems();
        return Inertia::render('WelcomeMenu', ['menuItems' => $menuItems]);
    }

}
