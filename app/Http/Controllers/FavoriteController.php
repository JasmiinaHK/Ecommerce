<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the user's favorite products.
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Load favorites with product relationships
        $favorites = $user->favorites()
            ->with([
                'primaryImage',
                'category' => function($query) {
                    $query->where('is_active', true);
                }
            ])
            ->withPivot('created_at')
            ->orderBy('pivot_created_at', 'desc')
            ->paginate(12);

        // Transform the collection to include the product data in a format expected by the view
        $favorites->getCollection()->transform(function ($product) {
            return (object)[
                'product' => $product,
                'created_at' => $product->pivot->created_at
            ];
        });

        return view('favorites.index', [
            'favorites' => $favorites,
            'title' => 'My Favorites',
            'breadcrumbs' => [
                ['name' => 'My Account', 'url' => route('profile.edit')],
                ['name' => 'My Favorites', 'url' => route('favorites.index')]
            ]
        ]);
    }

    /**
     * Get the count of user's favorite products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function count(): JsonResponse
    {
        $count = Auth::user()->favorites()->count();
        
        return response()->json([
            'status' => 'success',
            'count' => $count
        ]);
    }

    /**
     * Toggle favorite status for a product.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function toggle(Product $product)
    {
        $user = Auth::user();
        $isFavorite = $user->favorites()->where('product_id', $product->id)->exists();
        
        if ($isFavorite) {
            // Remove from favorites
            $user->favorites()->detach($product->id);
            $message = 'Product removed from favorites';
            $status = 'removed';
        } else {
            // Add to favorites
            $user->favorites()->attach($product->id, [
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $message = 'Product added to favorites';
            $status = 'added';
        }

        $favoritesCount = $user->favorites()->count();
        $productFavoritesCount = $product->favoritedBy()->count();

        if (request()->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'action' => $status,
                'message' => $message,
                'favorites_count' => $favoritesCount,
                'product_favorites_count' => $productFavoritesCount
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove a product from favorites.
     */
    public function destroy(Product $product): JsonResponse
    {
        Auth::user()->favorites()->detach($product->id);

        $favoritesCount = Auth::user()->favorites()->count();
        $productFavoritesCount = $product->favoritedBy()->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from favorites',
            'favorites_count' => $favoritesCount,
            'product_favorites_count' => $productFavoritesCount - 1
        ]);
    }

    /**
     * Check if a product is in the user's favorites.
     */
    public function check(Product $product): JsonResponse
    {
        $isFavorite = Auth::user()->favorites()->where('product_id', $product->id)->exists();
        
        return response()->json([
            'status' => 'success',
            'is_favorite' => $isFavorite,
            'favorites_count' => $product->favoritedBy()->count()
        ]);
    }


}
