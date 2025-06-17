<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_description',
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
        'options' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_price',
        'formatted_total',
        'options_text'
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
     * Get the order that owns the order item.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that owns the order item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
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
     * Get the product name.
     * If the product is soft-deleted, return the stored name.
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->product_name ?? ($this->product ? $this->product->name : 'Unavailable Product');
    }

    /**
     * Get the product description.
     * If the product is soft-deleted, return the stored description.
     *
     * @return string
     */
    public function getProductDescription()
    {
        return $this->product_description ?? ($this->product ? $this->product->description : '');
    }

    /**
     * Get the product image URL.
     * Returns null if the product is deleted or has no image.
     *
     * @return string|null
     */
    public function getProductImage()
    {
        if (!$this->product) {
            return null;
        }

        return $this->product->getImageUrl();
    }

    /**
     * Get the subtotal for the order item.
     *
     * @return float
     */
    public function getSubtotal()
    {
        return $this->price * $this->quantity;
    }

    /**
     * Increment the quantity.
     *
     * @param  int  $amount
     * @return bool
     */
    public function incrementQuantity($amount = 1)
    {
        $this->quantity += $amount;
        $this->total = $this->price * $this->quantity;
        return $this->save();
    }

    /**
     * Decrement the quantity.
     *
     * @param  int  $amount
     * @return bool
     */
    public function decrementQuantity($amount = 1)
    {
        if ($this->quantity <= $amount) {
            return $this->delete();
        }

        $this->quantity -= $amount;
        $this->total = $this->price * $this->quantity;
        return $this->save();
    }
}
