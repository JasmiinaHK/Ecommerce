<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'image_path',
        'alt_text',
        'is_primary',
        'sort_order'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a new image, set it as primary if it's the first image for the product
        static::creating(function ($image) {
            if (!ProductImage::where('product_id', $image->product_id)->exists()) {
                $image->is_primary = true;
            }
        });

        // When an image is set as primary, update other images of the same product
        static::saving(function ($image) {
            if ($image->is_primary) {
                static::where('product_id', $image->product_id)
                    ->where('id', '!=', $image->id)
                    ->update(['is_primary' => false]);
            }
        });

        // When an image is deleted, update the primary image if needed
        static::deleting(function ($image) {
            if ($image->is_primary) {
                $newPrimary = static::where('product_id', $image->product_id)
                    ->where('id', '!=', $image->id)
                    ->orderBy('sort_order')
                    ->first();

                if ($newPrimary) {
                    $newPrimary->update(['is_primary' => true]);
                }
            }
        });
    }

    /**
     * Get the product that owns the image.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the full URL to the image.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        return Storage::disk('public')->url($this->image_path);
    }

    /**
     * Scope a query to only include primary images.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Set the image as primary.
     *
     * @return bool
     */
    public function setAsPrimary()
    {
        return $this->update(['is_primary' => true]);
    }

    /**
     * Get the image path with a default if empty.
     *
     * @return string
     */
    public function getImagePathAttribute($value)
    {
        return $value ?: 'images/default-product.png';
    }

    /**
     * Get the alt text with a default if empty.
     *
     * @return string
     */
    public function getAltTextAttribute($value)
    {
        return $value ?: $this->product->name ?? 'Product Image';
    }
}
