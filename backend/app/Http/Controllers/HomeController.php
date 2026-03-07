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
        // ── Homepage Section Visibility (from Admin Settings) ─────────────────────
        $showHeroSlider      = store_setting('show_hero_slider',       '1') === '1';
        $showFlashSale       = store_setting('show_flash_sale',        '1') === '1';
        $showAiRecs          = store_setting('show_ai_recs',           '1') === '1';
        $showCategoryShowcase= store_setting('show_category_showcase', '1') === '1';
        $showFeatured        = store_setting('show_featured',          '1') === '1';
        $showTrending        = store_setting('show_trending',          '1') === '1';
        $showNewArrivals     = store_setting('show_new_arrivals',      '1') === '1';
        $showBrands          = store_setting('show_brands',            '1') === '1';
        $showNewsletter      = store_setting('show_newsletter',        '1') === '1';

        // ── Core data (always loaded) ──────────────────────────────────────────────
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $heroSlides = $showHeroSlider ? HeroSlide::active()->get() : collect();

        // Lazy-load heavy data only when sections are visible
        $featured = ($showFeatured || $showAiRecs)
            ? Product::where('is_featured', true)->where('is_active', true)->with('category')->limit(8)->get()
            : collect();

        $newArrivals = $showNewArrivals
            ? Product::where('is_active', true)->latest()->limit(8)->get()
            : collect();

        // ── Flash Sale ─────────────────────────────────────────────────────────────
        $activeDeal         = null;
        $flashSale          = collect();
        $flashSaleEnds      = now()->endOfDay()->timestamp;
        $activeDealDiscount = 0;

        if ($showFlashSale) {
            $activeDeal = FlashDeal::active()
                ->with(['products' => fn ($q) => $q->where('is_active', true)])
                ->latest('starts_at')->first();

            if ($activeDeal && $activeDeal->products->count()) {
                $flashSale = $activeDeal->products->take(6);
            } else {
                // Only use compare_price fallback if an active deal exists
                // No active deal → $flashSale stays empty → section hidden
                if ($activeDeal) {
                    $flashSale = Product::where('is_active', true)
                        ->whereNotNull('compare_price')->orderByDesc('rating')->limit(6)->get();
                }
            }
            $flashSaleEnds      = $activeDeal ? $activeDeal->ends_at->timestamp : now()->endOfDay()->timestamp;
            $activeDealDiscount = $activeDeal?->discount_percent ?? 0;
        }

        // ── AI Popular Products (ClickHouse-powered, for ALL visitors) ─────────────
        $aiPopular = collect();
        if ($showAiRecs || $showTrending) {
            $aiPopularRaw = $this->aiService->getPopularProducts(8);
            if (!empty($aiPopularRaw)) {
                $ids = array_column($aiPopularRaw, 'product_id');
                $aiPopular = Product::whereIn('id', $ids)->where('is_active', true)->get()
                    ->sortBy(fn($p) => array_search($p->id, $ids))->values();
            }
            if ($aiPopular->isEmpty()) $aiPopular = $featured;
        }

        // ── AI Trending Products (ClickHouse event-based, last 7 days) ────────────
        $aiTrending       = collect();
        $aiTrendingIsLive = false;
        if ($showTrending) {
            $aiTrendingRaw = $this->aiService->getTrendingProducts(8, 7);
            if (!empty($aiTrendingRaw)) {
                $ids = array_column($aiTrendingRaw, 'product_id');
                $aiTrending = Product::whereIn('id', $ids)->where('is_active', true)->get()
                    ->sortBy(fn($p) => array_search($p->id, $ids))->values();
                $aiTrendingIsLive = $aiTrending->isNotEmpty();
            }
            if ($aiTrending->isEmpty()) {
                $aiTrending = Product::where('is_active', true)->orderByDesc('reviews_count')->limit(8)->get();
            }
        }

        // ── Personalized Recommendations (collaborative filtering for logged-in) ───
        $recommendations = collect();
        $isPersonalized  = false;
        if ($showAiRecs) {
            if (auth()->check()) {
                try {
                    $recs = $this->aiService->getRecommendations(auth()->id(), 8);
                    if (!empty($recs)) {
                        $recIds = array_column($recs, 'product_id');
                        $recommendations = Product::whereIn('id', $recIds)->where('is_active', true)->get()
                            ->sortBy(fn($p) => array_search($p->id, $recIds))->values();
                        $isPersonalized = $recommendations->isNotEmpty();
                    }
                } catch (\Exception $e) {}
            }
            if ($recommendations->isEmpty()) $recommendations = $aiPopular;
        }

        $trending = $aiTrending;

        return view('home.index', compact(
            'categories', 'heroSlides', 'featured', 'newArrivals', 'trending',
            'flashSale', 'recommendations', 'flashSaleEnds', 'activeDeal', 'activeDealDiscount',
            'aiPopular', 'aiTrending', 'aiTrendingIsLive', 'isPersonalized',
            // Section visibility flags
            'showHeroSlider', 'showFlashSale', 'showAiRecs', 'showCategoryShowcase',
            'showFeatured', 'showTrending', 'showNewArrivals', 'showBrands', 'showNewsletter'
        ));
    }
}
