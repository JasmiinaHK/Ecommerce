<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index(): View
    {
        $orders = Auth::user()
            ->orders()
            ->with(['items.product.primaryImage'])
            ->latest()
            ->paginate(10);

        return view('orders.index', [
            'orders' => $orders,
            'title' => 'My Orders',
            'breadcrumbs' => [
                ['name' => 'My Account', 'url' => route('profile.edit')],
                ['name' => 'My Orders', 'url' => route('orders.index')]
            ]
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        return view('orders.show', [
            'order' => $order->load(['items.product.primaryImage']),
            'title' => 'Order #' . $order->order_number,
            'breadcrumbs' => [
                ['name' => 'My Account', 'url' => route('profile.edit')],
                ['name' => 'My Orders', 'url' => route('orders.index')],
                ['name' => 'Order #' . $order->order_number, 'url' => route('orders.show', $order)]
            ]
        ]);
    }

    /**
     * Show the checkout form.
     */
    public function checkout(): View|RedirectResponse
    {
        $cart = $this->getOrCreateCart();
        
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty. Please add some products before checking out.');
        }

        // Check product availability
        foreach ($cart->items as $item) {
            if (!$item->product || $item->product->quantity_available < $item->quantity) {
                return redirect()->route('cart.index')
                    ->with('error', 'Some items in your cart are no longer available in the requested quantity.');
            }
        }

        $user = Auth::user();
        
        return view('checkout.index', [
            'cart' => $cart,
            'user' => $user,
            'title' => 'Checkout',
            'breadcrumbs' => [
                ['name' => 'Cart', 'url' => route('cart.index')],
                ['name' => 'Checkout', 'url' => route('checkout')]
            ]
        ]);
    }

    /**
     * Process the checkout form and create an order.
     */
    public function processCheckout(Request $request): RedirectResponse
    {
        $cart = $this->getOrCreateCart();
        
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty. Please add some products before checking out.');
        }

        // Validate the request
        $validated = $request->validate([
            'billing_first_name' => 'required|string|max:255',
            'billing_last_name' => 'required|string|max:255',
            'billing_email' => 'required|email|max:255',
            'billing_phone' => 'required|string|max:20',
            'billing_address' => 'required|string|max:500',
            'billing_city' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',
            'billing_post_code' => 'nullable|string|max:20',
            'shipping_different' => 'boolean',
            'shipping_first_name' => 'required_if:shipping_different,1|string|max:255|nullable',
            'shipping_last_name' => 'required_if:shipping_different,1|string|max:255|nullable',
            'shipping_email' => 'required_if:shipping_different,1|email|max:255|nullable',
            'shipping_phone' => 'required_if:shipping_different,1|string|max:20|nullable',
            'shipping_address' => 'required_if:shipping_different,1|string|max:500|nullable',
            'shipping_city' => 'required_if:shipping_different,1|string|max:255|nullable',
            'shipping_country' => 'required_if:shipping_different,1|string|max:255|nullable',
            'shipping_post_code' => 'nullable|string|max:20',
            'payment_method' => 'required|in:credit_card,paypal,bank_transfer,cash_on_delivery',
            'terms' => 'accepted',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check product availability again before processing payment
        $unavailableItems = [];
        foreach ($cart->items as $item) {
            if (!$item->product || $item->product->quantity_available < $item->quantity) {
                $unavailableItems[] = $item->name;
            }
        }

        if (!empty($unavailableItems)) {
            return redirect()->route('cart.index')
                ->with('error', 'The following items are no longer available in the requested quantity: ' . implode(', ', $unavailableItems));
        }

        // Start transaction
        DB::beginTransaction();

        try {
            // Create the order
            $orderData = [
                'user_id' => Auth::id(),
                'status' => 'pending',
                'grand_total' => $cart->total,
                'item_count' => $cart->items->count(),
                'payment_status' => false,
                'payment_method' => $validated['payment_method'],
                'billing_first_name' => $validated['billing_first_name'],
                'billing_last_name' => $validated['billing_last_name'],
                'billing_email' => $validated['billing_email'],
                'billing_phone' => $validated['billing_phone'],
                'billing_address' => $validated['billing_address'],
                'billing_city' => $validated['billing_city'],
                'billing_country' => $validated['billing_country'],
                'billing_post_code' => $validated['billing_post_code'] ?? null,
                'shipping_different' => $validated['shipping_different'] ?? false,
                'shipping_first_name' => $validated['shipping_first_name'] ?? null,
                'shipping_last_name' => $validated['shipping_last_name'] ?? null,
                'shipping_email' => $validated['shipping_email'] ?? null,
                'shipping_phone' => $validated['shipping_phone'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'shipping_city' => $validated['shipping_city'] ?? null,
                'shipping_country' => $validated['shipping_country'] ?? null,
                'shipping_post_code' => $validated['shipping_post_code'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ];

            $order = Order::create($orderData);

            // Add order items
            foreach ($cart->items as $item) {
                $orderItem = new OrderItem([
                    'product_id' => $item->product_id,
                    'product_name' => $item->name,
                    'product_description' => $item->product->description ?? null,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                    'options' => $item->options
                ]);

                $order->items()->save($orderItem);

                // Update product quantity
                $item->product->decrement('quantity', $item->quantity);
            }

            // Clear the cart
            $cart->items()->delete();
            $cart->recalculateTotals();

            // Commit the transaction
            DB::commit();

            // Process payment based on payment method
            // This is a simplified example - in a real app, you would integrate with a payment gateway
            $paymentStatus = $this->processPayment($order, $request);

            if ($paymentStatus['success']) {
                $order->update([
                    'payment_status' => true,
                    'status' => 'processing',
                    'transaction_id' => $paymentStatus['transaction_id'] ?? null
                ]);

                // Send order confirmation email
                // Mail::to($order->billing_email)->send(new OrderConfirmation($order));


                return redirect()->route('orders.show', $order)
                    ->with('success', 'Your order has been placed successfully!');
            } else {
                // Payment failed
                return redirect()->back()
                    ->with('error', 'Payment failed: ' . ($paymentStatus['message'] ?? 'Unknown error'))
                    ->withInput();
            }

        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
            \Log::error('Checkout error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'An error occurred while processing your order. Please try again.')
                ->withInput();
        }
    }

    /**
     * Process payment for an order.
     */
    protected function processPayment(Order $order, Request $request): array
    {
        // This is a simplified example
        // In a real app, you would integrate with a payment gateway like Stripe, PayPal, etc.
        
        // For demo purposes, we'll simulate a successful payment
        return [
            'success' => true,
            'transaction_id' => 'TXN-' . strtoupper(\Illuminate\Support\Str::random(10)),
            'message' => 'Payment processed successfully'
        ];
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
            )->load('items.product');
        }

        return Cart::firstOrCreate(
            ['session_id' => session()->getId()],
            ['user_id' => null]
        )->load('items.product');
    }
}
