<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'primaryImage'])
            ->where('is_active', true);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('category', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'featured':
                $query->where('is_featured', true);
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12);
        $categories = Category::where('is_active', true)->get();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'search' => $request->input('search'),
                'category' => $request->input('category'),
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
                'sort' => $sort,
            ]
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): View
    {
        if (!$product->is_active) {
            abort(404);
        }

        // Load related products (products from the same category)
        $relatedProducts = Product::with('primaryImage')
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->take(4)
            ->get();

        // Increment view count
        $product->increment('views');

        return view('products.show', [
            'product' => $product->load(['images', 'category']),
            'relatedProducts' => $relatedProducts,
        ]);
    }

    /**
     * Toggle product favorite status.
     */
    public function toggleFavorite(Product $product): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You need to be logged in to add to favorites.'
            ], 401);
        }

        $user = auth()->user();
        $isFavorite = $user->favorites()->where('product_id', $product->id)->exists();

        if ($isFavorite) {
            $user->favorites()->detach($product->id);
            $message = 'Product removed from favorites.';
            $status = 'removed';
        } else {
            $user->favorites()->attach($product->id);
            $message = 'Product added to favorites.';
            $status = 'added';
        }

        return response()->json([
            'status' => 'success',
            'action' => $status,
            'favorites_count' => $product->favoritedBy()->count(),
            'message' => $message
        ]);
    }

    /**
     * Search products by name or description.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'status' => 'error',
                'message' => 'Search query must be at least 2 characters long.'
            ], 422);
        }

        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->where('is_active', true)
            ->with('primaryImage')
            ->take(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'products' => $products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->formatted_price,
                    'image' => $product->primaryImage ? $product->primaryImage->image_url : null,
                    'url' => route('products.show', $product->slug)
                ];
            })
        ]);
    }
}
