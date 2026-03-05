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
