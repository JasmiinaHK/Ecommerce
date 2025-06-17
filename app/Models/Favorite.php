<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Favorite extends Pivot
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'favorites';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'product_id'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the favorite.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that is favorited.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Toggle favorite status for a product.
     *
     * @param  int  $userId
     * @param  int  $productId
     * @return array
     */
    public static function toggleFavorite($userId, $productId)
    {
        $existingFavorite = static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existingFavorite) {
            $existingFavorite->delete();
            return ['status' => 'removed'];
        }

        static::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['status' => 'added'];
    }

    /**
     * Check if a product is favorited by a user.
     *
     * @param  int  $userId
     * @param  int  $productId
     * @return bool
     */
    public static function isFavorited($userId, $productId)
    {
        return static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Get the count of favorites for a user.
     *
     * @param  int  $userId
     * @return int
     */
    public static function countByUser($userId)
    {
        return static::where('user_id', $userId)->count();
    }

    /**
     * Get the count of times a product has been favorited.
     *
     * @param  int  $productId
     * @return int
     */
    public static function countByProduct($productId)
    {
        return static::where('product_id', $productId)->count();
    }
}
