<?php

/**
 * Global helper functions for TechNova Store.
 * Autoloaded via composer.json files[] entry.
 */

if (!function_exists('format_currency')) {
    /**
     * Format a monetary amount using store currency settings.
     * Default: ৳ (BDT), no decimals.
     *
     * @param  float|int|string  $amount
     * @param  int               $decimals
     * @return string  e.g. "৳1,299"
     */
    function format_currency($amount, int $decimals = 0): string
    {
        try {
            $symbol   = \App\Models\Setting::get('currency_symbol', '৳');
            $position = \App\Models\Setting::get('currency_position', 'before');
        } catch (\Throwable $e) {
            $symbol   = '৳';
            $position = 'before';
        }

        $formatted = number_format((float) $amount, $decimals, '.', ',');

        return $position === 'after'
            ? $formatted . ' ' . $symbol
            : $symbol . $formatted;
    }
}

if (!function_exists('currency_symbol')) {
    /** Return the store currency symbol (e.g. ৳) */
    function currency_symbol(): string
    {
        try {
            return \App\Models\Setting::get('currency_symbol', '৳');
        } catch (\Throwable $e) {
            return '৳';
        }
    }
}

if (!function_exists('store_setting')) {
    /** Shorthand to read any store setting with optional default. */
    function store_setting(string $key, $default = null)
    {
        try {
            return \App\Models\Setting::get($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (!function_exists('storage_url')) {
    /**
     * Convert a stored image path to a correct absolute URL for the current domain.
     * Handles: relative paths (uploads/...), old absolute localhost URLs, external URLs.
     *
     * @param  string|null  $path
     * @return string
     */
    function storage_url(?string $path): string
    {
        if (empty($path)) return '';
        // Old absolute URL containing /storage/ → strip domain, rebuild with APP_URL
        if (str_starts_with($path, 'http') && str_contains($path, '/storage/')) {
            $relativePath = \Illuminate\Support\Str::after($path, '/storage/');
            return \Illuminate\Support\Facades\Storage::disk('public')->url($relativePath);
        }
        // External URL (picsum, unsplash, CDN) → return as-is
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        // Relative path → build URL using APP_URL
        return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    }
}
