<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    /**
     * Order statuses.
     *
     * @var array
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DECLINED = 'declined';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Payment methods.
     *
     * @var array
     */
    const PAYMENT_METHOD_CASH_ON_DELIVERY = 'cash_on_delivery';
    const PAYMENT_METHOD_PAYPAL = 'paypal';
    const PAYMENT_METHOD_STRIPE = 'stripe';
    const PAYMENT_METHOD_CARD = 'card';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'grand_total',
        'item_count',
        'payment_status',
        'payment_method',
        'transaction_id',
        'billing_first_name',
        'billing_last_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_city',
        'billing_country',
        'billing_post_code',
        'shipping_different',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_country',
        'shipping_post_code',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'grand_total' => 'decimal:2',
        'item_count' => 'integer',
        'payment_status' => 'boolean',
        'shipping_different' => 'boolean',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_number = 'ORD-' . strtoupper(Str::random(10));
        });
    }

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the billing full name.
     *
     * @return string
     */
    public function getBillingFullNameAttribute()
    {
        return "{$this->billing_first_name} {$this->billing_last_name}";
    }

    /**
     * Get the shipping full name.
     *
     * @return string
     */
    public function getShippingFullNameAttribute()
    {
        if (!$this->shipping_different) {
            return $this->billing_full_name;
        }
        return "{$this->shipping_first_name} {$this->shipping_last_name}";
    }

    /**
     * Get the formatted grand total.
     *
     * @return string
     */
    public function getFormattedGrandTotalAttribute()
    {
        return '€' . number_format($this->grand_total, 2);
    }

    /**
     * Get the formatted status.
     *
     * @return string
     */
    public function getFormattedStatusAttribute()
    {
        return ucfirst($this->status);
    }

    /**
     * Get the formatted payment method.
     *
     * @return string
     */
    public function getFormattedPaymentMethodAttribute()
    {
        return str_replace('_', ' ', ucfirst($this->payment_method));
    }

    /**
     * Check if the order is pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the order is processing.
     *
     * @return bool
     */
    public function isProcessing()
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if the order is completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the order is declined.
     *
     * @return bool
     */
    public function isDeclined()
    {
        return $this->status === self::STATUS_DECLINED;
    }

    /**
     * Check if the order is cancelled.
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Scope a query to only include pending orders.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include processing orders.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope a query to only include completed orders.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Create an order from a cart.
     *
     * @param  \App\Models\Cart  $cart
     * @param  array  $data
     * @return \App\Models\Order
     */
    public static function createFromCart(Cart $cart, array $data)
    {
        $order = self::create([
            'user_id' => $cart->user_id,
            'status' => self::STATUS_PENDING,
            'grand_total' => $cart->total,
            'item_count' => $cart->items->count(),
            'payment_status' => false,
            'payment_method' => $data['payment_method'] ?? self::PAYMENT_METHOD_CASH_ON_DELIVERY,
            'billing_first_name' => $data['billing_first_name'],
            'billing_last_name' => $data['billing_last_name'],
            'billing_email' => $data['billing_email'],
            'billing_phone' => $data['billing_phone'],
            'billing_address' => $data['billing_address'],
            'billing_city' => $data['billing_city'],
            'billing_country' => $data['billing_country'],
            'billing_post_code' => $data['billing_post_code'] ?? null,
            'shipping_different' => $data['shipping_different'] ?? false,
            'shipping_first_name' => $data['shipping_first_name'] ?? null,
            'shipping_last_name' => $data['shipping_last_name'] ?? null,
            'shipping_email' => $data['shipping_email'] ?? null,
            'shipping_phone' => $data['shipping_phone'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? null,
            'shipping_city' => $data['shipping_city'] ?? null,
            'shipping_country' => $data['shipping_country'] ?? null,
            'shipping_post_code' => $data['shipping_post_code'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        // Add order items
        foreach ($cart->items as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'product_name' => $item->name,
                'product_description' => $item->product->description ?? null,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'total' => $item->total,
                'options' => $item->options
            ]);

            // Update product quantity
            if ($item->product) {
                $item->product->decrement('quantity', $item->quantity);
            }
        }

        // Clear the cart
        $cart->clear();

        return $order;
    }

    /**
     * Mark the order as paid.
     *
     * @param  string  $transactionId
     * @return bool
     */
    public function markAsPaid($transactionId = null)
    {
        return $this->update([
            'payment_status' => true,
            'status' => $this->status === self::STATUS_PENDING ? self::STATUS_PROCESSING : $this->status,
            'transaction_id' => $transactionId
        ]);
    }

    /**
     * Update the order status.
     *
     * @param  string  $status
     * @return bool
     */
    public function updateStatus($status)
    {
        if (!in_array($status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_COMPLETED,
            self::STATUS_DECLINED,
            self::STATUS_CANCELLED
        ])) {
            return false;
        }

        return $this->update(['status' => $status]);
    }

    /**
     * Get the payment status as a string.
     *
     * @return string
     */
    public function getPaymentStatusTextAttribute()
    {
        return $this->payment_status ? 'Paid' : 'Unpaid';
    }
}
