<?php

namespace App\Models;

use App\Traits\ResolvesStorageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, ResolvesStorageUrl;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'short_description',
        'price', 'compare_price', 'stock', 'sku', 'brand', 'rating',
        'reviews_count', 'image', 'specs', 'is_featured', 'is_active',
        'is_flash_deal', 'flash_deal_ends_at', 'flash_deal_discount',
    ];

    protected $casts = [
        'price'              => 'decimal:2',
        'compare_price'      => 'decimal:2',
        'rating'             => 'decimal:2',
        'specs'              => 'array',
        'is_featured'        => 'boolean',
        'is_active'          => 'boolean',
        'is_flash_deal'      => 'boolean',
        'flash_deal_ends_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getDiscountPercentAttribute()
    {
        if ($this->compare_price && $this->compare_price > $this->price) {
            return round((($this->compare_price - $this->price) / $this->compare_price) * 100);
        }
        return 0;
    }

    public function getIsInStockAttribute()
    {
        return $this->stock > 0;
    }

    public function getImageAttribute($value): ?string
    {
        return self::resolveStorageUrl($value);
    }
}
