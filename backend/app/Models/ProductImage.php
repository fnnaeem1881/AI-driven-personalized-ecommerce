<?php

namespace App\Models;

use App\Traits\ResolvesStorageUrl;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use ResolvesStorageUrl;

    protected $fillable = ['product_id', 'image_path', 'sort_order'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImagePathAttribute($value): ?string
    {
        return self::resolveStorageUrl($value);
    }
}
