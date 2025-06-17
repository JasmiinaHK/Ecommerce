<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/debug/product/{id}', function($id) {
    $product = Product::with(['category', 'images'])->findOrFail($id);
    
    // Check if product has a category
    if (!$product->category) {
        return 'Product has no category';
    }
    
    // Check if category has a name
    if (!isset($product->category->name)) {
        return 'Category has no name property';
    }
    
    return 'Product category: ' . $product->category->name;
});
