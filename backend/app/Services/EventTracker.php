<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EventTracker
{
    private string $eventServiceUrl;

    public function __construct()
    {
        $this->eventServiceUrl = config('services.events.url', 'http://localhost:8000');
    }

    public function track(string $eventType, array $data = []): void
    {
        try {
            $event = [
                'event_id'   => (string) Str::uuid(),
                'event_type' => $eventType,
                'user_id'    => auth()->id(),
                'session_id' => session()->getId(),
                'timestamp'  => now()->toIso8601String(),
                'data'       => $data,
                'ip_address' => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            ];

            // Send directly to event service via HTTP (fast local call, fails silently if service down)
            Http::connectTimeout(1)
                ->timeout(3)
                ->post("{$this->eventServiceUrl}/collect", $event);

        } catch (\Throwable $e) {
            Log::warning('Event tracking failed: ' . $e->getMessage());
        }
    }
}
