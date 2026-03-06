<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ResolvesStorageUrl
{
    /**
     * Convert a stored image value to a correct absolute URL for the current domain.
     *
     * Handles three cases:
     *  1. Relative path (e.g. "uploads/2026/03/file.png") → uses APP_URL from filesystems config
     *  2. Old absolute URL with hardcoded domain (e.g. "http://localhost:8000/storage/uploads/...")
     *     → strips the old domain and rebuilds with current APP_URL
     *  3. External URL (e.g. "https://picsum.photos/...") → returned unchanged
     */
    protected static function resolveStorageUrl(?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }

        if (str_starts_with($value, 'http')) {
            // Local file stored with a hardcoded domain — fix it
            if (str_contains($value, '/storage/')) {
                $relativePath = Str::after($value, '/storage/');
                return Storage::disk('public')->url($relativePath);
            }
            // External URL (picsum, CDN, etc.) — return as-is
            return $value;
        }

        // Relative path — generate URL using APP_URL
        return Storage::disk('public')->url($value);
    }
}
