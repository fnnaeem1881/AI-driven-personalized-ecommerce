<?php

namespace App\Models;

use App\Traits\ResolvesStorageUrl;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use ResolvesStorageUrl;
    protected $fillable = ['order_id', 'product_id', 'product_name', 'product_image', 'price', 'quantity', 'total'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getProductImageAttribute($value): ?string
    {
        return self::resolveStorageUrl($value);
    }
}
