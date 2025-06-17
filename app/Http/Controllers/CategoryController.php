<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(): View
    {
        $categories = Category::withCount(['products' => function($query) {
                $query->where('is_active', true);
            }])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(12); // Changed from get() to paginate()


        return view('categories.index', [
            'categories' => $categories,
            'title' => 'All Categories',
            'description' => 'Browse our wide range of product categories.'
        ]);
    }

    /**
     * Display the specified category with its products.
     */
    public function show(Request $request, string $slug): View
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $query = $category->products()
            ->with('primaryImage')
            ->where('is_active', true);

        // Apply sorting
        $sort = $request->input('sort', 'newest');
        $this->applySorting($query, $sort);

        $products = $query->paginate(12);

        return view('categories.show', [
            'category' => $category,
            'products' => $products,
            'sort' => $sort,
            'title' => $category->name,
            'description' => $category->description ?? 'Browse our selection of ' . $category->name
        ]);
    }

    /**
     * Get all categories for navigation or dropdown.
     */
    public function getCategories(): JsonResponse
    {
        $categories = Category::with(['children' => function($query) {
                $query->where('is_active', true)
                    ->orderBy('name');
            }])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'categories' => $categories->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->image_url,
                    'children' => $category->children->map(function($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                            'image' => $child->image_url,
                        ];
                    })
                ];
            })
        ]);
    }

    /**
     * Apply sorting to the query.
     */
    protected function applySorting($query, string $sort): void
    {
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
    }
}
