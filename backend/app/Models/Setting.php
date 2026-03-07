<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null): mixed
    {
        // Only cache when the key actually exists in DB; return $default without caching otherwise
        $cached = Cache::get("setting_{$key}", '__MISS__');
        if ($cached !== '__MISS__') {
            return $cached;
        }

        $setting = static::where('key', $key)->first();
        if ($setting) {
            Cache::put("setting_{$key}", $setting->value, 3600);
            return $setting->value;
        }

        return $default; // not cached — will re-query next time
    }

    public static function set(string $key, $value): void
    {
        Cache::forget("setting_{$key}");
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function getAll(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }
}
