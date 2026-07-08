<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TableSessionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/db-check', function () {
    DB::table('migrations')->first();
    return 'Database connected successfully!';
});
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'welcome']);

// Optional split pages (same landing data)
Route::get('/categories', [HomeController::class, 'categories'])->name('categories');
Route::get('/menu', [HomeController::class, 'menu'])->name('menu');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/cart', [HomeController::class, 'cart'])->name('cart');

Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');

// Complete the order - no separate page needed
Route::post('/cart/complete', [HomeController::class, 'completeOrder'])->name('cart.complete');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('users')
    ->name('users')
    ->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store'])->name('.store');
        Route::patch('/{user}', [UserController::class, 'update'])->name('.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('.destroy');
    });

Route::prefix('menu-categories')
    ->name('menu-categories')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', [MenuCategoryController::class, 'index']);
        Route::post('/', [MenuCategoryController::class, 'store'])->name('.store');
        Route::patch('/{category}', [MenuCategoryController::class, 'update'])->name('.update');
        Route::delete('/{category}', [MenuCategoryController::class, 'destroy'])->name('.destroy');
    });

Route::prefix('menu-items')
    ->name('menu-items')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', [MenuItemController::class, 'index']);
        Route::post('/', [MenuItemController::class, 'store'])->name('.store');
        Route::patch('/{item}', [MenuItemController::class, 'update'])->name('.update');
        Route::delete('/{item}', [MenuItemController::class, 'destroy'])->name('.destroy');
    });

Route::prefix('tables')
    ->name('tables')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', [TableController::class, 'index']);
        Route::post('/', [TableController::class, 'store'])->name('.store');
        Route::patch('/{table}', [TableController::class, 'update'])->name('.update');
        Route::delete('/{table}', [TableController::class, 'destroy'])->name('.destroy');
    });

Route::prefix('table-sessions')
    ->name('table-sessions')
    ->group(function () {
        Route::get('/{table}', [TableSessionController::class, 'show'])->name('.show');
        Route::post('/{table}/close', [TableSessionController::class, 'close'])->name('.close');
    });

Route::get('/t/{payload}', [TableSessionController::class, 'show'])->name('table.entry');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
