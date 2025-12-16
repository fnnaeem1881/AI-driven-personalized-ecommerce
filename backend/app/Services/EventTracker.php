<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EventTracker
{
    public function track(string $eventType, array $data = []): void
    {
        try {
            $event = [
                'event_id' => (string) Str::uuid(),
                'event_type' => $eventType,
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'timestamp' => now()->toIso8601String(),
                'data' => $data,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];

            // Push to Redis queue
            Redis::rpush('events:queue', json_encode($event));
            
        } catch (\Exception $e) {
            Log::error('Event tracking failed: ' . $e->getMessage());
        }
    }
}