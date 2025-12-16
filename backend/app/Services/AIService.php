<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.ai.url', 'http://localhost:8001');
    }

    public function getRecommendations(int $userId, int $limit = 10): array
    {
        $cacheKey = "ai_recommendations:{$userId}:{$limit}";
        
        return Cache::remember($cacheKey, 1800, function () use ($userId, $limit) {
            try {
                $response = Http::timeout(5)
                    ->post("{$this->baseUrl}/recommendations/collaborative", [
                        'user_id' => $userId,
                        'limit' => $limit,
                    ]);

                if ($response->successful()) {
                    return $response->json()['recommendations'] ?? [];
                }
            } catch (\Exception $e) {
                Log::error('AI Service error: ' . $e->getMessage());
            }

            return [];
        });
    }

    public function getSimilarProducts(int $productId, int $limit = 6): array
    {
        $cacheKey = "ai_similar:{$productId}:{$limit}";
        
        return Cache::remember($cacheKey, 3600, function () use ($productId, $limit) {
            try {
                $response = Http::timeout(3)
                    ->post("{$this->baseUrl}/recommendations/similar", [
                        'product_id' => $productId,
                        'limit' => $limit,
                    ]);

                if ($response->successful()) {
                    return $response->json()['similar_products'] ?? [];
                }
            } catch (\Exception $e) {
                Log::error('Similar products error: ' . $e->getMessage());
            }

            return [];
        });
    }

    public function predictCartAbandonment(string $sessionId): array
    {
        try {
            $response = Http::timeout(2)
                ->post("{$this->baseUrl}/predictions/cart-abandonment", [
                    'session_id' => $sessionId,
                    'user_id' => auth()->id(),
                ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Cart prediction error: ' . $e->getMessage());
        }

        return ['risk_level' => 'unknown', 'probability' => 0];
    }
}