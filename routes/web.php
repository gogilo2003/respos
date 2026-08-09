<?php

use App\Http\Controllers\Api\PollingController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CashReconciliationController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TableSessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaiterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'welcome']);

// Optional split pages (same landing data)
Route::get('/categories', [HomeController::class, 'categories'])->name('categories');
Route::get('/menu', [HomeController::class, 'menu'])->name('menu');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/cart', [HomeController::class, 'cart'])->name('cart');

Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');

// Complete the order - no separate page needed
Route::post('/cart/complete', [HomeController::class, 'completeOrder'])->name('cart.complete');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('users')
    ->name('users')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store'])->name('.store');
        Route::patch('/{user}', [UserController::class, 'update'])->name('.update');
        Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('.toggle-status');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('.destroy');
    });

Route::prefix('menu-categories')
    ->name('menu-categories')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', [MenuCategoryController::class, 'index']);
        Route::post('/', [MenuCategoryController::class, 'store'])->name('.store');
        Route::patch('/{category}', [MenuCategoryController::class, 'update'])->name('.update');
        Route::patch('/{category}/toggle-active', [MenuCategoryController::class, 'toggleActive'])->name('.toggle-active');
        Route::delete('/{category}', [MenuCategoryController::class, 'destroy'])->name('.destroy');
    });

Route::prefix('menu-items')
    ->name('menu-items')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', [MenuItemController::class, 'index']);
        Route::post('/', [MenuItemController::class, 'store'])->name('.store');
        Route::patch('/{item}', [MenuItemController::class, 'update'])->name('.update');
        Route::patch('/{item}/toggle-availability', [MenuItemController::class, 'toggleAvailability'])->name('.toggle-availability');
        Route::delete('/{item}', [MenuItemController::class, 'destroy'])->name('.destroy');
    });

Route::prefix('tables')
    ->name('tables')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', [TableController::class, 'index']);
        Route::post('/', [TableController::class, 'store'])->name('.store');
        Route::patch('/{table}', [TableController::class, 'update'])->name('.update');
        Route::get('/{table}/qr-image', [TableController::class, 'qrImage'])->name('.qr-image');
        Route::post('/{table}/regenerate-qr', [TableController::class, 'regenerateQr'])->name('.regenerate-qr');
        Route::delete('/{table}', [TableController::class, 'destroy'])->name('.destroy');
    });

Route::prefix('table-sessions')
    ->name('table-sessions')
    ->group(function () {
        Route::get('/{table}', [TableSessionController::class, 'show'])->name('.show');
        Route::post('/{table}/close', [TableSessionController::class, 'close'])->name('.close');
    });

Route::patch('/orders/{order}/status', [OrderController::class, 'transition'])
    ->middleware(['auth'])
    ->name('orders.status.update');

Route::get('/session/{table}', [TableSessionController::class, 'show'])->name('session.entry');
Route::get('/orders/{order}/track', [CustomerOrderController::class, 'track'])->name('orders.track');

Route::prefix('bills')
    ->name('bills')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', [BillController::class, 'index'])->name('.index');
        Route::post('/', [BillController::class, 'store'])->name('.store');
        Route::get('/{bill}', [BillController::class, 'show'])->name('.show');
        Route::patch('/{bill}/void', [BillController::class, 'void'])->name('.void');
        Route::delete('/{bill}', [BillController::class, 'destroy'])->name('.destroy');
    });

Route::prefix('payments')
    ->name('payments')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::post('/bills/{bill}', [PaymentController::class, 'store'])->name('.store');
    });

Route::prefix('kitchen')
    ->name('kitchen')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/dashboard', [KitchenController::class, 'dashboard'])->name('.dashboard');
        Route::patch('/order-items/{orderItem}', [KitchenController::class, 'updateItemStatus'])->name('.item.update');
        Route::patch('/orders/{order}/ready', [KitchenController::class, 'markOrderReady'])->name('.order.ready');
    });

Route::prefix('waiter')
    ->name('waiter')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/dashboard', [WaiterController::class, 'dashboard'])->name('.dashboard');
        Route::post('/orders', [WaiterController::class, 'storeOrder'])->name('.orders.store');
        Route::get('/assistance-requests', [WaiterController::class, 'assistanceRequests'])->name('.assistance.index');
        Route::patch('/assistance-requests/{assistanceRequest}', [WaiterController::class, 'updateAssistance'])->name('.assistance.update');
        Route::patch('/order-items/{orderItem}/served', [WaiterController::class, 'serveOrderItem'])->name('.order-items.served');
    });

Route::get('/t/{payload}', [TableSessionController::class, 'show'])->name('table.entry');

Route::prefix('api/v1/polling')->group(function () {
    Route::get('/updates', [PollingController::class, 'updates'])->name('api.polling.updates');
    Route::post('/notifications/{id}/read', [PollingController::class, 'markRead'])->name('api.polling.mark-read');
});

Route::prefix('reconciliations')
    ->name('reconciliations')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', [CashReconciliationController::class, 'index'])->name('.index');
        Route::post('/', [CashReconciliationController::class, 'store'])->name('.store');
        Route::post('/{id}/approve', [CashReconciliationController::class, 'approve'])->name('.approve');
    });

Route::get('/audit-logs', [AuditLogController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('audit-logs.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
