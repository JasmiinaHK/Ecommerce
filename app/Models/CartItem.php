<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'name',
        'price',
        'quantity',
        'total',
        'options'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'options' => 'array'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total = $item->price * $item->quantity;
        });
    }

    /**
     * Get the cart that owns the item.
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product that owns the item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the formatted price attribute.
     *
     * @return string
     */
    public function getFormattedPriceAttribute()
    {
        return '€' . number_format($this->price, 2);
    }

    /**
     * Get the formatted total attribute.
     *
     * @return string
     */
    public function getFormattedTotalAttribute()
    {
        return '€' . number_format($this->total, 2);
    }

    /**
     * Get the options as a formatted string.
     *
     * @return string
     */
    public function getOptionsTextAttribute()
    {
        if (empty($this->options)) {
            return '';
        }

        $options = [];
        foreach ($this->options as $key => $value) {
            $options[] = ucfirst($key) . ': ' . $value;
        }

        return implode(', ', $options);
    }

    /**
     * Update the quantity and recalculate the total.
     *
     * @param  int  $quantity
     * @return bool
     */
    public function updateQuantity($quantity)
    {
        if ($quantity <= 0) {
            return $this->delete();
        }

        $this->quantity = $quantity;
        $this->total = $this->price * $quantity;
        
        return $this->save();
    }

    /**
     * Increment the quantity by the given amount.
     *
     * @param  int  $amount
     * @return bool
     */
    public function incrementQuantity($amount = 1)
    {
        return $this->updateQuantity($this->quantity + $amount);
    }

    /**
     * Decrement the quantity by the given amount.
     *
     * @param  int  $amount
     * @return bool
     */
    public function decrementQuantity($amount = 1)
    {
        return $this->updateQuantity($this->quantity - $amount);
    }
}
