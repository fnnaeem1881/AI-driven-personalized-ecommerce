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

    /**
     * Get full AI profile for a user (segment, recommendations, interactions, recent events).
     * Used by admin panel user detail page.
     */
    public function getUserProfile(int $userId, int $limit = 10): array
    {
        try {
            $response = Http::timeout(8)
                ->get("{$this->baseUrl}/users/{$userId}/profile", ['limit' => $limit]);

            if ($response->successful()) {
                return $response->json() ?? [];
            }
        } catch (\Exception $e) {
            Log::error('AI user profile error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get health status of the AI service and its dependencies.
     */
    public function getHealth(): array
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/health");
            if ($response->successful()) {
                return $response->json() ?? [];
            }
        } catch (\Exception $e) {}

        return ['status' => 'down', 'services' => ['clickhouse' => false, 'redis' => false], 'models' => []];
    }

    /**
     * Get AI model performance metrics.
     */
    public function getModelPerformance(): array
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/analytics/model-performance");
            if ($response->successful()) {
                return $response->json() ?? [];
            }
        } catch (\Exception $e) {}

        return [];
    }
}