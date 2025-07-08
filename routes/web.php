<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication Routes
require __DIR__.'/auth.php';

// Debug route
Route::get('/debug-product/{id}', function($id) {
    $product = \App\Models\Product::with('category')->find($id);
    
    if (!$product) {
        return 'Product not found';
    }
    
    echo '<h1>Product: ' . e($product->name) . '</h1>';
    
    if ($product->category) {
        echo '<p>Category: ' . e($product->category->name) . '</p>';
    } else {
        echo '<p>No category assigned</p>';
    }
    
    // Check database directly
    $category = \DB::table('categories')->find($product->category_id);
    echo '<h2>Direct DB Check:</h2>';
    echo '<pre>' . print_r($category, true) . '</pre>';
    
    return '';
});


// Product Routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Category Routes
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

// Authenticated User Routes
Route::middleware(['auth'])->group(function () {
    // Cart Routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    // Favorite Routes
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/toggle/{product}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/favorites/count', [FavoriteController::class, 'count'])->name('favorites.count');

    // Order Routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/place', [OrderController::class, 'placeOrder'])->name('orders.place');
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/process', [OrderController::class, 'processCheckout'])->name('checkout.process');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Admin dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])
            ->name('dashboard');

        // Resource routes for admin
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class);
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        
        // Additional admin routes
        Route::get('orders/{order}/invoice', [\App\Http\Controllers\Admin\OrderController::class, 'invoice'])
            ->name('orders.invoice');
        Route::post('orders/{order}/update-status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])
            ->name('orders.update-status');
    });
