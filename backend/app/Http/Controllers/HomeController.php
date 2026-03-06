<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Services\AIService;

class HomeController extends Controller
{
    public function __construct(private AIService $aiService) {}

    public function index()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $heroSlides = HeroSlide::active()->get();

        $featured = Product::where('is_featured', true)->where('is_active', true)
            ->with('category')->limit(8)->get();

        $newArrivals = Product::where('is_active', true)->latest()->limit(8)->get();

        // Flash deals: use active FlashDeal entity first, fallback to compare_price products
        $activeDeal   = FlashDeal::active()->with(['products' => fn ($q) => $q->where('is_active', true)])->latest('starts_at')->first();
        $flashSale    = $activeDeal && $activeDeal->products->count()
            ? $activeDeal->products->take(6)
            : Product::where('is_active', true)->whereNotNull('compare_price')->orderByDesc('rating')->limit(6)->get();
        $flashSaleEnds = $activeDeal ? $activeDeal->ends_at->timestamp : now()->endOfDay()->timestamp;
        $activeDealDiscount = $activeDeal?->discount_percent ?? 0;

        // ── AI Popular Products (ClickHouse-powered, shown to ALL visitors) ────────
        $aiPopularRaw = $this->aiService->getPopularProducts(8);
        $aiPopular = collect();
        if (!empty($aiPopularRaw)) {
            $ids = array_column($aiPopularRaw, 'product_id');
            $aiPopular = Product::whereIn('id', $ids)->where('is_active', true)->get()
                ->sortBy(fn($p) => array_search($p->id, $ids))->values();
        }
        if ($aiPopular->isEmpty()) {
            $aiPopular = $featured;
        }

        // ── AI Trending Products (ClickHouse event-based, last 7 days) ────────────
        $aiTrendingRaw    = $this->aiService->getTrendingProducts(8, 7);
        $aiTrendingIsLive = false;
        $aiTrending = collect();
        if (!empty($aiTrendingRaw)) {
            $ids = array_column($aiTrendingRaw, 'product_id');
            $aiTrending = Product::whereIn('id', $ids)->where('is_active', true)->get()
                ->sortBy(fn($p) => array_search($p->id, $ids))->values();
            $aiTrendingIsLive = $aiTrending->isNotEmpty();
        }
        if ($aiTrending->isEmpty()) {
            // Fallback: MySQL reviews_count-based trending
            $aiTrending = Product::where('is_active', true)->orderByDesc('reviews_count')->limit(8)->get();
        }

        // ── Personalized Recommendations (collaborative filtering for logged-in users)
        $recommendations = collect();
        $isPersonalized  = false;
        if (auth()->check()) {
            try {
                $recs = $this->aiService->getRecommendations(auth()->id(), 8);
                if (!empty($recs)) {
                    $recIds = array_column($recs, 'product_id');
                    $recommendations = Product::whereIn('id', $recIds)->where('is_active', true)->get()
                        ->sortBy(fn($p) => array_search($p->id, $recIds))->values();
                    $isPersonalized = $recommendations->isNotEmpty();
                }
            } catch (\Exception $e) {
                // Fallback silently
            }
        }
        // Fallback for anonymous / new users: use ClickHouse popular products
        if ($recommendations->isEmpty()) {
            $recommendations = $aiPopular;
        }

        // Keep $trending as ClickHouse-powered for backward compat in view
        $trending = $aiTrending;

        return view('home.index', compact(
            'categories', 'heroSlides', 'featured', 'newArrivals', 'trending',
            'flashSale', 'recommendations', 'flashSaleEnds', 'activeDeal', 'activeDealDiscount',
            'aiPopular', 'aiTrending', 'aiTrendingIsLive', 'isPersonalized'
        ));
    }
}
