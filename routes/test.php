<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/test-product/{id}', function($id) {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Get the product with category
    $product = Product::with('category')->find($id);
    
    if (!$product) {
        return "Product not found";
    }
    
    // Debug output
    echo "<h1>Debug Product #{$product->id}</h1>";
    echo "<h2>Product Info:</h2>";
    echo "<pre>" . print_r($product->toArray(), true) . "</pre>";
    
    echo "<h2>Category Info:</h2>";
    if ($product->category) {
        echo "<pre>" . print_r($product->category->toArray(), true) . "</pre>";
    } else {
        echo "No category found for this product";
    }
    
    // Try to access category name
    try {
        echo "<h2>Category Name:</h2>";
        echo $product->category ? $product->category->name : 'No category';
    } catch (\Exception $e) {
        echo "<div style='color:red;'>Error: " . $e->getMessage() . "</div>";
    }
});
