<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Display the user's shopping cart.
     */
    public function index(): View
    {
        $cart = $this->getOrCreateCart();
        
        return view('cart.index', [
            'cart' => $cart,
            'items' => $cart->items()->with('product.primaryImage')->get(),
            'title' => 'Your Shopping Cart',
            'breadcrumbs' => [
                ['name' => 'Cart', 'url' => route('cart.index')]
            ]
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request, Product $product): JsonResponse
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1|max:' . $product->quantity,
                'options' => 'nullable|array'
            ]);

            // Check if product is active and in stock
            if (!$product->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This product is not available for purchase.'
                ], 400);
            }


            $cart = $this->getOrCreateCart();
            $quantity = $request->input('quantity', 1);
            $options = $request->input('options', []);

            // Check if product already exists in cart
            $existingItem = $cart->items()
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                // Check if we have enough stock
                $newQuantity = $existingItem->quantity + $quantity;
                if ($newQuantity > $product->quantity) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Not enough stock available. Only ' . $product->quantity . ' items left in stock.'
                    ], 400);
                }

                // Update quantity if item already exists
                $existingItem->update([
                    'quantity' => $newQuantity,
                    'total' => $product->price * $newQuantity
                ]);
            } else {
                // Check if we have enough stock
                if ($quantity > $product->quantity) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Not enough stock available. Only ' . $product->quantity . ' items left in stock.'
                    ], 400);
                }

                // Add new item to cart
                $cart->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'total' => $product->price * $quantity,
                    'options' => $options
                ]);
            }

            // Recalculate cart totals
            $cart->recalculateTotals();
            $cart->load('items.product.primaryImage');

            // Get updated cart data
            $cartData = [
                'item_count' => $cart->items->sum('quantity'),
                'formatted_subtotal' => number_format($cart->subtotal, 2) . ' €',
                'formatted_total' => number_format($cart->total, 2) . ' €',
                'items' => $cart->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $item->name,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'total' => $item->total,
                        'image' => $item->product->primaryImage ? asset('storage/' . $item->product->primaryImage->image_path) : asset('images/placeholder.png'),
                        'url' => route('products.show', $item->product_id)
                    ];
                })
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Product added to cart',
                'cart' => $cartData
            ]);

        } catch (\Exception $e) {
            \Log::error('Error adding to cart: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while adding the product to cart. Please try again.'
            ], 500);
        }
    }

    /**
     * Update the specified cart item.
     */
    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->authorize('update', $cartItem);
        
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $cartItem->product->quantity_available
        ]);

        $quantity = $request->input('quantity');
        $cartItem->update([
            'quantity' => $quantity,
            'total' => $cartItem->price * $quantity
        ]);

        // Recalculate cart totals
        $cart = $cartItem->cart;
        $cart->recalculateTotals();

        return response()->json([
            'status' => 'success',
            'message' => 'Cart updated',
            'item' => [
                'id' => $cartItem->id,
                'formatted_total' => $cartItem->formatted_total,
                'formatted_price' => $cartItem->formatted_price,
                'quantity' => $cartItem->quantity
            ],
            'cart' => [
                'item_count' => $cart->item_count,
                'formatted_subtotal' => $cart->formatted_subtotal,
                'formatted_tax' => $cart->formatted_tax,
                'formatted_shipping' => $cart->formatted_shipping,
                'formatted_total' => $cart->formatted_total
            ]
        ]);
    }

    /**
     * Remove the specified item from cart.
     */
    public function remove(CartItem $cartItem): JsonResponse
    {
        $this->authorize('delete', $cartItem);
        
        $cart = $cartItem->cart;
        $cartItem->delete();

        // Recalculate cart totals
        $cart->recalculateTotals();

        return response()->json([
            'status' => 'success',
            'message' => 'Item removed from cart',
            'cart' => [
                'item_count' => $cart->item_count,
                'formatted_subtotal' => $cart->formatted_subtotal,
                'formatted_total' => $cart->formatted_total
            ]
        ]);
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): JsonResponse
    {
        $cart = $this->getOrCreateCart();
        $cart->items()->delete();
        $cart->recalculateTotals();

        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared',
            'cart' => [
                'item_count' => 0,
                'formatted_subtotal' => '€0.00',
                'formatted_total' => '€0.00'
            ]
        ]);
    }

    /**
     * Get the cart count.
     */
    public function count(): JsonResponse
    {
        $cart = $this->getOrCreateCart();
        
        return response()->json([
            'status' => 'success',
            'count' => $cart->item_count
        ]);
    }

    /**
     * Get or create a cart for the current user or session.
     */
    protected function getOrCreateCart(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                ['session_id' => session()->getId()]
            );
        }

        return Cart::firstOrCreate(
            ['session_id' => session()->getId()],
            ['user_id' => null]
        );
    }
}
