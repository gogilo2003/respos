<?php

use App\Http\Controllers\OrderController;

Route::post('/public/orders', [OrderController::class, 'store'])->name('public.orders.store');
Route::get('/public/orders/{orderId}', [OrderController::class, 'status'])->name('public.orders.status');