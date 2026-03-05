<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashDeal extends Model
{
    protected $fillable = [
        'title',
        'description',
        'discount_percent',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
        'is_active'        => 'boolean',
        'discount_percent' => 'integer',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'flash_deal_product');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)
                 ->where('starts_at', '<=', now())
                 ->where('ends_at', '>=', now());
    }

    public function isRunning(): bool
    {
        return $this->is_active
            && $this->starts_at <= now()
            && $this->ends_at >= now();
    }
}
