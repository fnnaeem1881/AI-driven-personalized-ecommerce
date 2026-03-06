<?php
namespace App\Models;
use App\Traits\ResolvesStorageUrl;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    use ResolvesStorageUrl;
    protected $fillable = [
        'badge','badge_color','title','subtitle','description',
        'image','cta_text','cta_link','cta_secondary_text',
        'cta_secondary_link','sort_order','is_active',
    ];
    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query) {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getImageAttribute($value): ?string
    {
        return self::resolveStorageUrl($value);
    }
}
