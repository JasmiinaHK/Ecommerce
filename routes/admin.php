<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;

Route::group([
    'as' => 'admin.',
    'prefix' => 'admin',
    'middleware' => ['web', 'auth', 'admin']
], function() {
    // Admin Dashboard
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Categories
    Route::resource('categories', CategoryController::class);
    Route::get('categories/datatable', [CategoryController::class, 'datatable'])->name('categories.datatable');

    // Products
    Route::resource('products', ProductController::class);
    Route::get('products/datatable', [ProductController::class, 'datatable'])->name('products.datatable');

    // Orders
    Route::resource('orders', OrderController::class);
    Route::get('orders/datatable', [OrderController::class, 'datatable'])->name('orders.datatable');
});
