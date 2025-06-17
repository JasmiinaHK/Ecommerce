<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the home page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // Get featured products with category and primary image
            $featuredProducts = Product::with([
                    'primaryImage' => function($query) {
                        $query->where('is_primary', true);
                    },
                    'category' => function($query) {
                        $query->where('is_active', true);
                    }
                ])
                ->where('is_featured', true)
                ->where('is_active', true)
                ->whereHas('category', function($query) {
                    $query->where('is_active', true);
                })
                ->take(8)
                ->get()
                ->filter(function($product) {
                    // Ensure product exists and has required relationships
                    return $product && 
                           $product->exists && 
                           $product->category && 
                           $product->category->exists;
                });

            // Get latest products with category and primary image
            $latestProducts = Product::with([
                    'primaryImage' => function($query) {
                        $query->where('is_primary', true);
                    },
                    'category' => function($query) {
                        $query->where('is_active', true);
                    }
                ])
                ->where('is_active', true)
                ->whereHas('category', function($query) {
                    $query->where('is_active', true);
                })
                ->latest()
                ->take(8)
                ->get()
                ->filter(function($product) {
                    // Ensure product exists and has required relationships
                    return $product && 
                           $product->exists && 
                           $product->category && 
                           $product->category->exists;
                });

            // Get categories with active products and their count
            $categories = Category::withCount(['products' => function($query) {
                    $query->where('is_active', true);
                }])
                ->where('is_active', true)
                ->whereHas('products', function($query) {
                    $query->where('is_active', true);
                })
                ->with(['products' => function($query) {
                    $query->where('is_active', true)
                          ->with(['primaryImage' => function($q) {
                              $q->where('is_primary', true);
                          }]);
                }])
                ->take(6)
                ->get()
                ->filter(function($category) {
                    return $category && $category->exists && $category->products->isNotEmpty();
                });

            return view('home', [
                'featuredProducts' => $featuredProducts,
                'latestProducts' => $latestProducts,
                'categories' => $categories,
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error in HomeController@index: ' . $e->getMessage());
            
            // Return a safe view with empty collections
            return view('home', [
                'featuredProducts' => collect(),
                'latestProducts' => collect(),
                'categories' => collect(),
            ]);
        }
    }
}
