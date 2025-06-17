<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class Cart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'subtotal',
        'tax',
        'shipping',
        'discount',
        'total',
        'coupon_code',
        'coupon_discount',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the cart.
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the current cart instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Models\Cart
     */
    public static function getCart($request = null)
    {
        $user = auth()->user();
        $sessionId = Session::getId();
        
        if ($user) {
            // If user is logged in, get or create their cart
            $cart = static::firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => $sessionId]
            );
            
            // If there's a session cart, merge it with the user's cart
            if ($sessionId && $sessionCart = static::where('session_id', $sessionId)->whereNull('user_id')->first()) {
                $cart->mergeCart($sessionCart);
            }
        } else {
            // For guests, get or create a cart based on session
            $cart = static::firstOrCreate(
                ['session_id' => $sessionId],
                ['user_id' => null]
            );
        }
        
        return $cart;
    }

    /**
     * Merge another cart into this one.
     *
     * @param  \App\Models\Cart  $cartToMerge
     * @return void
     */
    public function mergeCart(Cart $cartToMerge)
    {
        if ($this->id === $cartToMerge->id) {
            return;
        }

        foreach ($cartToMerge->items as $item) {
            $existingItem = $this->items()->where('product_id', $item->product_id)->first();
            
            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $item->quantity,
                    'total' => $existingItem->price * ($existingItem->quantity + $item->quantity)
                ]);
            } else {
                $this->items()->create([
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                    'options' => $item->options
                ]);
            }
        }

        $this->recalculateTotals();
        $cartToMerge->delete();
    }

    /**
     * Add an item to the cart.
     *
     * @param  \App\Models\Product  $product
     * @param  int  $quantity
     * @param  array  $options
     * @return \App\Models\CartItem
     */
    public function addItem(Product $product, $quantity = 1, $options = [])
    {
        $item = $this->items()->updateOrCreate(
            [
                'product_id' => $product->id,
                'options' => !empty($options) ? json_encode($options) : null
            ],
            [
                'name' => $product->name,
                'price' => $product->current_price,
                'quantity' => \DB::raw("quantity + {$quantity}"),
                'total' => \DB::raw("price * (quantity + {$quantity})")
            ]
        );

        $this->recalculateTotals();
        
        return $item;
    }

    /**
     * Update the quantity of an item in the cart.
     *
     * @param  int  $itemId
     * @param  int  $quantity
     * @return bool
     */
    public function updateItemQuantity($itemId, $quantity)
    {
        $item = $this->items()->findOrFail($itemId);
        
        if ($quantity <= 0) {
            return $this->removeItem($itemId);
        }
        
        $item->update([
            'quantity' => $quantity,
            'total' => $item->price * $quantity
        ]);
        
        $this->recalculateTotals();
        
        return true;
    }

    /**
     * Remove an item from the cart.
     *
     * @param  int  $itemId
     * @return bool
     */
    public function removeItem($itemId)
    {
        $this->items()->where('id', $itemId)->delete();
        $this->recalculateTotals();
        
        return true;
    }

    /**
     * Clear all items from the cart.
     *
     * @return bool
     */
    public function clear()
    {
        $this->items()->delete();
        $this->update([
            'subtotal' => 0,
            'tax' => 0,
            'shipping' => 0,
            'discount' => 0,
            'total' => 0,
            'coupon_code' => null,
            'coupon_discount' => 0
        ]);
        
        return true;
    }

    /**
     * Recalculate cart totals.
     *
     * @return void
     */
    public function recalculateTotals()
    {
        $subtotal = $this->items()->sum('total');
        $discount = $this->coupon_discount;
        $shipping = $this->calculateShipping();
        $tax = $this->calculateTax($subtotal - $discount);
        $total = $subtotal + $tax + $shipping - $discount;
        
        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'total' => $total
        ]);
    }

    /**
     * Calculate shipping cost.
     *
     * @return float
     */
    protected function calculateShipping()
    {
        // Implement your shipping calculation logic here
        return 0;
    }

    /**
     * Calculate tax.
     *
     * @param  float  $amount
     * @return float
     */
    protected function calculateTax($amount)
    {
        // Implement your tax calculation logic here
        // Example: 10% tax rate
        return $amount * 0.10;
    }

    /**
     * Apply a coupon to the cart.
     *
     * @param  string  $couponCode
     * @return bool
     */
    public function applyCoupon($couponCode)
    {
        // Implement your coupon validation and application logic here
        // This is a simplified example
        $discount = 10.00; // Example fixed discount
        
        $this->update([
            'coupon_code' => $couponCode,
            'coupon_discount' => $discount
        ]);
        
        $this->recalculateTotals();
        
        return true;
    }

    /**
     * Remove the applied coupon from the cart.
     *
     * @return bool
     */
    public function removeCoupon()
    {
        $this->update([
            'coupon_code' => null,
            'coupon_discount' => 0
        ]);
        
        $this->recalculateTotals();
        
        return true;
    }

    /**
     * Get the number of items in the cart.
     *
     * @return int
     */
    public function getItemCountAttribute()
    {
        return $this->items->sum('quantity');
    }
}
